<?php

namespace Tests\Feature;

use App\Actions\Analytics\AggregateDailyAnalyticsAction;
use App\Actions\Mailboxes\CreateMailboxAction;
use App\Models\AnalyticsDailyMetric;
use App\Models\AnalyticsEvent;
use App\Models\Domain;
use App\Models\Mailbox;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Services\Analytics\AnalyticsEventTracker;
use App\Services\Analytics\AnalyticsMetricRegistry;
use App\Services\Analytics\AnalyticsRetentionService;
use App\Services\Billing\MembershipExpiryService;
use App\Services\Billing\PlanSettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class AnalyticsFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_registered_events_can_be_tracked_with_privacy_sanitization(): void
    {
        $user = User::factory()->create();
        $event = app(AnalyticsEventTracker::class)->track('mailbox.created', [
            'user' => $user,
            'ip' => '203.0.113.10',
            'visitor_id' => 'visitor-123',
            'session_id' => 'session-123',
            'metadata' => [
                'source' => 'admin',
                'mailbox_type' => 'guest',
                'email' => 'person@example.test',
                'message_body' => 'private message body',
                'authorization' => 'Bearer secret-token',
                'secret' => 'secret-token',
                'ip' => '203.0.113.10',
            ],
        ]);

        $this->assertSame('mailbox.created', $event->event_key);
        $this->assertSame($user->id, $event->user_id);
        $this->assertSame(64, strlen((string) $event->ip_hash));
        $this->assertSame(64, strlen((string) $event->visitor_hash));
        $this->assertSame(['source' => 'admin', 'mailbox_type' => 'guest'], $event->metadata);

        $stored = json_encode(AnalyticsEvent::query()->firstOrFail()->toArray());
        $this->assertStringNotContainsString('person@example.test', $stored);
        $this->assertStringNotContainsString('private message body', $stored);
        $this->assertStringNotContainsString('secret-token', $stored);
        $this->assertStringNotContainsString('203.0.113.10', $stored);
    }

    public function test_unknown_events_are_rejected_but_safe_tracking_does_not_throw(): void
    {
        $tracker = app(AnalyticsEventTracker::class);

        $this->expectException(InvalidArgumentException::class);
        $tracker->track('unknown.event');
    }

    public function test_safe_tracking_rejects_unknown_events_without_breaking_workflow(): void
    {
        $result = app(AnalyticsEventTracker::class)->trackSafely('unknown.event', [
            'metadata' => ['source' => 'test'],
        ]);

        $this->assertNull($result);
        $this->assertDatabaseCount('analytics_events', 0);
    }

    public function test_daily_aggregation_creates_metric_rows(): void
    {
        $tracker = app(AnalyticsEventTracker::class);
        $tracker->track('mailbox.created', ['visitor_id' => 'one', 'metadata' => ['source' => 'test']]);
        $tracker->track('mailbox.created', ['visitor_id' => 'one', 'metadata' => ['source' => 'test']]);
        $tracker->track('mailbox.expired', ['visitor_id' => 'two', 'metadata' => ['source' => 'test']]);

        $result = app(AggregateDailyAnalyticsAction::class)->handle(today());

        $this->assertSame(today()->toDateString(), $result['date']);
        $this->assertSame(2, $result['rows']);
        $this->assertDatabaseHas('analytics_daily_metrics', [
            'event_key' => 'mailbox.created',
            'total_count' => 2,
            'unique_visitors' => 1,
        ]);
        $this->assertSame(2, AnalyticsDailyMetric::query()->count());
    }

    public function test_module_hooks_track_mailbox_membership_and_rate_limit_events(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);

        app(CreateMailboxAction::class)->handle($admin, [
            'local_part' => 'analytics',
            'domain_id' => $domain->id,
            'mailbox_type' => 'guest',
        ], '198.51.100.9');

        $mailbox = Mailbox::query()->firstOrFail();
        $this->get(route('admin.mailbox-operations.show', $mailbox))->assertRedirect(route('login'));
        $this->actingAs($admin)->get(route('admin.mailbox-operations.show', $mailbox))->assertOk();

        $this->assertDatabaseHas('analytics_events', ['event_key' => 'mailbox.created']);
        $this->assertDatabaseHas('analytics_events', ['event_key' => 'inbox.viewed']);

        $this->grantPremium($admin);
        $this->assertDatabaseHas('analytics_events', ['event_key' => 'premium.granted']);

        Membership::query()->firstOrFail()->forceFill([
            'status' => 'active',
            'ends_at' => now()->subDay(),
        ])->save();
        app(MembershipExpiryService::class)->process();
        $this->assertDatabaseHas('analytics_events', ['event_key' => 'premium.expired']);
    }

    public function test_retention_readiness_and_command_are_available(): void
    {
        $readiness = app(AnalyticsRetentionService::class)->readiness();

        $this->assertSame(90, $readiness['raw_event_retention_days']);
        $this->assertSame('analytics_daily_metrics', $readiness['aggregate_storage']);
        $this->artisan('analytics:aggregate-daily')->assertSuccessful();
    }

    public function test_registered_event_catalog_contains_initial_events(): void
    {
        $events = app(AnalyticsMetricRegistry::class)->events();

        foreach ([
            'mailbox.created',
            'mailbox.email_received',
            'mailbox.expired',
            'inbox.viewed',
            'blog.viewed',
            'user.registered',
            'premium.granted',
            'premium.expired',
            'comment.submitted',
            'security.rate_limited',
        ] as $eventKey) {
            $this->assertArrayHasKey($eventKey, $events);
        }
    }

    private function domain(User $actor): Domain
    {
        return Domain::query()->create([
            'domain_name' => 'analytics.example',
            'display_name' => 'Analytics',
            'is_active' => true,
            'is_public' => true,
            'catch_all_ready' => true,
            'status' => 'ready',
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }

    private function grantPremium(User $admin): void
    {
        app(PlanSettingsStore::class)->ensureDefaults();
        $member = User::factory()->create(['current_plan_reference' => 'free']);
        $premium = Plan::query()->where('key', 'premium')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.plans-memberships.memberships.grant'), [
            'user_id' => $member->id,
            'plan_id' => $premium->id,
            'preset' => 'custom',
            'starts_at' => now()->subMonths(2)->format('Y-m-d\TH:i'),
            'ends_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ])->assertRedirect();
    }
}
