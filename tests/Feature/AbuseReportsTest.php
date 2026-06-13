<?php

namespace Tests\Feature;

use App\Models\AbuseReport;
use App\Models\User;
use App\Models\UserAuditEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AbuseReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_public_abuse_report_form_renders_with_accessible_intake(): void
    {
        $this->get(route('abuse-report.create'))
            ->assertOk()
            ->assertSee('Report abuse')
            ->assertSee('Trust and safety')
            ->assertSee(route('abuse-report.store'), false)
            ->assertSee('aria-invalid=', false);
    }

    public function test_public_submission_validates_sanitizes_and_generates_non_guessable_reference(): void
    {
        $response = $this->from(route('abuse-report.create'))->post(route('abuse-report.store'), [
            'report_type' => 'phishing',
            'reporter_name' => 'Security Reporter',
            'reporter_email' => 'reporter@example.com',
            'subject' => 'Suspicious mailbox impersonation',
            'description' => '<p>A mailbox is impersonating our support team with a suspicious public URL.</p>',
            'related_url' => 'https://example.com/report/notice',
        ]);

        $report = AbuseReport::query()->firstOrFail();

        $response->assertRedirect(route('abuse-report.create'))
            ->assertSessionHas('status', fn (string $status): bool => str_contains($status, $report->case_reference));

        $this->assertMatchesRegularExpression('/^AB-\d{6}-[A-Z0-9]{12}$/', $report->case_reference);
        $this->assertStringContainsString($report->case_reference, route('admin.abuse-reports.show', $report));
        $this->assertStringNotContainsString('/abuse-reports/'.$report->id, route('admin.abuse-reports.show', $report));
        $this->assertSame('A mailbox is impersonating our support team with a suspicious public URL.', $report->description);
        $this->assertSame(hash('sha256', 'reporter@example.com'), $report->reporter_email_hash);
        $this->assertNotNull($report->submitted_ip_hash);
        $this->assertSame('ready', $report->reporter_notification_status);
        $this->assertIsArray($report->bot_protection_readiness);

        $this->from(route('abuse-report.create'))->post(route('abuse-report.store'), [
            'report_type' => 'malware',
            'reporter_name' => 'Unsafe Reporter',
            'reporter_email' => 'unsafe@example.com',
            'subject' => 'Executable report payload',
            'description' => '<script>alert(1)</script> This payload should never be stored.',
        ])->assertSessionHasErrors('description');

        $this->assertDatabaseCount('abuse_reports', 1);
    }

    public function test_public_submission_is_rate_limited_and_does_not_create_security_signals(): void
    {
        $middleware = Route::getRoutes()->getByName('abuse-report.store')?->gatherMiddleware() ?? [];
        $this->assertContains('throttle:abuse_reports', $middleware);

        $this->post(route('abuse-report.store'), $this->payload())->assertRedirect();
        $this->assertDatabaseCount('abuse_reports', 1);
        $this->assertDatabaseCount('abuse_signals', 0);
    }

    public function test_high_and_critical_reports_notify_authorized_administrators(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post(route('abuse-report.store'), [...$this->payload(), 'priority' => 'critical'])
            ->assertRedirect();

        $report = AbuseReport::query()->firstOrFail();
        $this->assertDatabaseHas('system_notifications', [
            'recipient_user_id' => $admin->id,
            'event_key' => 'new_abuse_report',
            'severity' => 'critical',
            'target_type' => AbuseReport::class,
            'target_id' => $report->id,
        ]);
    }

    public function test_admin_queue_filters_and_case_detail_use_reference_route(): void
    {
        $admin = User::factory()->admin()->create();
        $matching = $this->report(['report_type' => 'phishing', 'priority' => 'critical', 'subject' => 'Priority phishing case']);
        $this->report(['report_type' => 'spam', 'priority' => 'low', 'subject' => 'Low spam case']);

        $this->actingAs($admin)
            ->get(route('admin.abuse-reports.index', ['report_type' => 'phishing', 'priority' => 'critical', 'q' => 'Priority']))
            ->assertOk()
            ->assertViewHas('reports', fn ($reports): bool => $reports->getCollection()->contains($matching) && $reports->count() === 1)
            ->assertSee('Abuse Reports')
            ->assertSee('Priority phishing case')
            ->assertDontSee('This workspace is ready for implementation.');

        $this->actingAs($admin)
            ->get(route('admin.abuse-reports.show', $matching))
            ->assertOk()
            ->assertSee($matching->case_reference)
            ->assertSee('No automatic blocking or deletion');

        $this->assertStringContainsString($matching->case_reference, route('admin.abuse-reports.show', $matching));
        $this->assertStringNotContainsString('/'.$matching->id, route('admin.abuse-reports.show', $matching));
    }

    public function test_assignment_and_status_updates_are_audited_without_private_description(): void
    {
        $admin = User::factory()->admin()->create();
        $moderator = User::factory()->create(['is_admin' => true, 'role' => 'moderator']);
        $report = $this->report(['description' => 'Private evidence narrative must not enter audit metadata.']);

        $this->actingAs($admin)
            ->put(route('admin.abuse-reports.assign', $report), ['assigned_to' => $moderator->id])
            ->assertRedirect();

        $this->actingAs($moderator)
            ->put(route('admin.abuse-reports.status', $report), ['status' => 'reviewing'])
            ->assertRedirect();

        $report->refresh();
        $this->assertSame($moderator->id, $report->assigned_to);
        $this->assertSame('reviewing', $report->status);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'abuse.case_assigned', 'target_id' => $report->id]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'abuse.case_status_changed', 'target_id' => $report->id]);

        $metadata = UserAuditEvent::query()->where('target_id', $report->id)->pluck('metadata')->toJson();
        $this->assertStringNotContainsString('Private evidence narrative', $metadata);
    }

    public function test_sensitive_reporter_information_is_hidden_from_moderators(): void
    {
        $moderator = User::factory()->create(['is_admin' => true, 'role' => 'moderator']);
        $admin = User::factory()->admin()->create();
        $report = $this->report(['reporter_email' => 'private-reporter@example.com']);

        $this->actingAs($moderator)
            ->get(route('admin.abuse-reports.show', $report))
            ->assertOk()
            ->assertSee('Contact information protected')
            ->assertDontSee('private-reporter@example.com');

        $this->actingAs($admin)
            ->get(route('admin.abuse-reports.show', $report))
            ->assertOk()
            ->assertSee('private-reporter@example.com');
    }

    public function test_normal_users_cannot_access_abuse_case_queue(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.abuse-reports.index'))
            ->assertForbidden();
    }

    public function test_abuse_report_sources_avoid_forbidden_patterns(): void
    {
        $files = [
            app_path('Http/Controllers/AbuseReportSubmissionController.php'),
            app_path('Http/Controllers/Admin/AbuseReportController.php'),
            app_path('Services/Abuse/AbuseReportStore.php'),
            app_path('Services/Abuse/AbuseCaseService.php'),
            resource_path('views/abuse-report/create.blade.php'),
            resource_path('views/dashboard/abuse-reports/index.blade.php'),
            resource_path('views/dashboard/abuse-reports/show.blade.php'),
            resource_path('views/components/abuse/case-card.blade.php'),
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            $this->assertIsString($contents);
            $this->assertStringNotContainsString('Livewire', $contents, $file);
            $this->assertStringNotContainsString('livewire', $contents, $file);
            $this->assertStringNotContainsString('cdn.tailwindcss.com', $contents, $file);
            $this->assertStringNotContainsString('unpkg.com/alpine', $contents, $file);
            $this->assertStringNotContainsString('127.0.0.1', $contents, $file);
        }
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'report_type' => 'phishing',
            'reporter_name' => 'Trust Reporter',
            'reporter_email' => 'trust@example.com',
            'subject' => 'Potential phishing mailbox',
            'description' => 'A public mailbox appears to be impersonating a trusted service and requesting credentials.',
        ];
    }

    /** @param array<string, mixed> $overrides */
    private function report(array $overrides = []): AbuseReport
    {
        $email = $overrides['reporter_email'] ?? 'reporter@example.com';

        return AbuseReport::query()->create([
            'case_reference' => 'AB-260613-'.str()->upper(str()->random(12)),
            'report_type' => 'other',
            'priority' => 'normal',
            'status' => 'new',
            'reporter_name' => 'Reporter',
            'reporter_email' => $email,
            'reporter_email_hash' => hash('sha256', $email),
            'subject' => 'Abuse report subject',
            'description' => 'Detailed human-submitted report description.',
            'description_excerpt' => 'Detailed human-submitted report description.',
            'reporter_notification_status' => 'ready',
            ...$overrides,
        ]);
    }
}
