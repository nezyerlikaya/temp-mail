<?php

namespace Tests\Feature;

use App\Models\AbuseReport;
use App\Models\Domain;
use App\Models\Mailbox;
use App\Models\MediaAsset;
use App\Models\User;
use App\Models\UserAuditEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AbuseCaseResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_case_detail_renders_part_two_workflow_components(): void
    {
        $admin = User::factory()->admin()->create();
        $report = $this->report();

        $this->actingAs($admin)->get(route('admin.abuse-reports.show', $report))
            ->assertOk()
            ->assertSee('Case timeline')
            ->assertSee('Internal notes')
            ->assertSee('Protected evidence')
            ->assertSee('Resolution decision')
            ->assertSee('Operational action')
            ->assertSee('Reporter response readiness');
    }

    public function test_internal_notes_are_sanitized_and_never_written_to_audit_metadata(): void
    {
        $admin = User::factory()->admin()->create();
        $report = $this->report();
        $secretNote = '<b>Investigate privately</b> with internal-token-9988';

        $this->actingAs($admin)->post(route('admin.abuse-reports.notes.store', $report), ['body' => $secretNote])->assertRedirect();

        $this->assertDatabaseHas('abuse_case_notes', ['abuse_report_id' => $report->id, 'body' => 'Investigate privately with internal-token-9988']);
        $this->assertDatabaseHas('abuse_case_events', ['abuse_report_id' => $report->id, 'event_type' => 'note_added']);
        $this->assertStringNotContainsString('internal-token-9988', UserAuditEvent::query()->where('event', 'abuse.note_added')->pluck('metadata')->toJson());
    }

    public function test_evidence_uses_private_copy_and_permission_controlled_download(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        Storage::disk('public')->put('media/evidence.pdf', 'protected-evidence');

        $admin = User::factory()->admin()->create();
        $moderator = User::factory()->create(['role' => 'moderator', 'is_admin' => true]);
        $report = $this->report();
        $asset = MediaAsset::query()->create([
            'uuid' => (string) str()->uuid(), 'original_name' => 'evidence.pdf', 'file_name' => 'evidence.pdf',
            'disk' => 'public', 'path' => 'media/evidence.pdf', 'mime_type' => 'application/pdf', 'size_bytes' => 18,
            'type' => 'document', 'status' => 'active', 'title' => 'Case evidence', 'uploaded_by' => $admin->id,
        ]);

        $this->actingAs($moderator)->post(route('admin.abuse-reports.evidence.store', $report), ['media_asset_id' => $asset->id])->assertForbidden();
        $this->actingAs($admin)->post(route('admin.abuse-reports.evidence.store', $report), ['media_asset_id' => $asset->id, 'label' => 'Private PDF'])->assertRedirect();

        $evidence = $report->evidences()->firstOrFail();
        Storage::disk('local')->assertExists($evidence->private_path);
        $this->assertStringStartsWith('abuse-evidence/'.$report->case_reference, $evidence->private_path);
        $this->actingAs($moderator)->get(route('admin.abuse-reports.evidence.download', [$report, $evidence]))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('admin.abuse-reports.evidence.download', [$report, $evidence]))->assertForbidden();

        $html = $this->actingAs($admin)->get(route('admin.abuse-reports.show', $report))->getContent();
        $this->assertStringNotContainsString('/storage/media/evidence.pdf', $html);
    }

    public function test_resolution_requires_reason_and_confirmation_and_prepares_safe_reporter_response(): void
    {
        $admin = User::factory()->admin()->create();
        $report = $this->report();
        $report->notes()->create(['author_id' => $admin->id, 'body' => 'Never expose this internal note.']);

        $route = route('admin.abuse-reports.resolve', $report);
        $this->actingAs($admin)->from(route('admin.abuse-reports.show', $report))->post($route, [
            'resolution_outcome' => 'no_action',
        ])->assertSessionHasErrors(['resolution_reason', 'confirm_resolution']);

        $this->actingAs($admin)->post($route, [
            'resolution_outcome' => 'warning_issued',
            'resolution_reason' => 'The reviewed evidence supports a warning without destructive action.',
            'resolution_summary' => 'A warning was recorded after review.',
            'reporter_response_body' => '<p>Your report was reviewed and a warning was issued.</p>',
            'confirm_resolution' => '1',
        ])->assertRedirect();

        $report->refresh();
        $this->assertSame('resolved', $report->status);
        $this->assertSame('warning_issued', $report->resolution_outcome);
        $this->assertSame('Your report was reviewed and a warning was issued.', $report->reporter_response_body);
        $this->assertStringNotContainsString('Never expose', (string) $report->reporter_response_body);
        $this->assertNotNull($report->retention_review_at);
        $this->assertStringNotContainsString('reviewed evidence supports', UserAuditEvent::query()->where('event', 'abuse.case_resolved')->pluck('metadata')->toJson());
    }

    public function test_terminal_statuses_cannot_bypass_reasoned_resolution_actions(): void
    {
        $admin = User::factory()->admin()->create();
        $report = $this->report();

        $this->actingAs($admin)->put(route('admin.abuse-reports.status', $report), ['status' => 'resolved'])
            ->assertSessionHasErrors('status');

        $this->assertSame('reviewing', $report->refresh()->status);
    }

    public function test_confirmed_mailbox_action_calls_owner_action_and_shares_correlation_id(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = Domain::query()->create(['domain_name' => 'abuse.test', 'display_name' => 'Abuse Test', 'status' => 'active']);
        $mailbox = Mailbox::query()->create(['domain_id' => $domain->id, 'address' => 'case@abuse.test', 'local_part' => 'case', 'status' => 'active']);
        $report = $this->report(['reported_mailbox_id' => $mailbox->id]);

        $route = route('admin.abuse-reports.operational-actions.execute', $report);
        $this->actingAs($admin)->post($route, ['operational_action' => 'lock_mailbox', 'reason' => 'Confirmed abuse review requires mailbox lock.'])
            ->assertSessionHasErrors('confirm_action');

        $this->actingAs($admin)->post($route, [
            'operational_action' => 'lock_mailbox',
            'reason' => 'Confirmed abuse review requires mailbox lock.',
            'confirm_action' => '1',
        ])->assertRedirect();

        $mailbox->refresh();
        $this->assertSame('locked', $mailbox->status);
        $this->assertSame('mailbox_locked', collect($mailbox->activity_timeline)->last()['event']);

        $mailboxAudit = UserAuditEvent::query()->where('event', 'mailbox.locked')->firstOrFail();
        $abuseAudit = UserAuditEvent::query()->where('event', 'abuse.operational_action_executed')->firstOrFail();
        $this->assertSame($mailboxAudit->correlation_id, $abuseAudit->correlation_id);
        $this->assertDatabaseHas('abuse_case_events', ['abuse_report_id' => $report->id, 'correlation_id' => $abuseAudit->correlation_id]);
    }

    public function test_blocklist_values_are_encrypted_and_audit_only_contains_preview(): void
    {
        $admin = User::factory()->admin()->create();
        $report = $this->report();
        $value = 'bad-actor@example.net';

        $this->actingAs($admin)->post(route('admin.abuse-reports.operational-actions.execute', $report), [
            'operational_action' => 'block_sender_email', 'block_value' => $value,
            'reason' => 'Confirmed sender abuse requires a blocklist entry.', 'confirm_action' => '1',
        ])->assertRedirect();

        $raw = (string) \DB::table('abuse_blocklist_entries')->value('encrypted_value');
        $this->assertStringNotContainsString($value, $raw);
        $this->assertStringNotContainsString($value, UserAuditEvent::query()->where('event', 'abuse.blocklist_entry_created')->pluck('metadata')->toJson());
        $this->assertDatabaseHas('abuse_blocklist_entries', ['type' => 'sender_email', 'value_hash' => hash('sha256', $value)]);
    }

    public function test_resolved_case_can_be_archived_and_reopened_with_retention_readiness(): void
    {
        $admin = User::factory()->admin()->create();
        $report = $this->report(['status' => 'resolved', 'resolution_outcome' => 'no_action', 'resolution_reason' => 'Previously resolved after a complete review.', 'resolved_by' => $admin->id, 'resolved_at' => now()]);

        $this->actingAs($admin)->post(route('admin.abuse-reports.archive', $report), ['reason' => 'Closed case is ready for retention scheduling.', 'confirm_archive' => '1'])->assertRedirect();
        $this->assertSame('archived', $report->refresh()->status);
        $this->assertNotNull($report->retention_review_at);

        $this->actingAs($admin)->post(route('admin.abuse-reports.reopen', $report), ['reason' => 'New information requires another investigation pass.', 'confirm_reopen' => '1'])->assertRedirect();
        $this->assertSame('reviewing', $report->refresh()->status);
        $this->assertNull($report->resolution_outcome);
        $this->assertDatabaseHas('abuse_case_events', ['abuse_report_id' => $report->id, 'event_type' => 'case_reopened']);
    }

    /** @param array<string, mixed> $overrides */
    private function report(array $overrides = []): AbuseReport
    {
        return AbuseReport::query()->create([
            'case_reference' => 'AB-260613-'.str()->upper(str()->random(12)), 'report_type' => 'phishing',
            'priority' => 'high', 'status' => 'reviewing', 'reporter_name' => 'Reporter',
            'reporter_email' => 'reporter@example.com', 'reporter_email_hash' => hash('sha256', 'reporter@example.com'),
            'subject' => 'Investigated abuse case', 'description' => 'Private case description.',
            'description_excerpt' => 'Private case description.', 'reporter_notification_status' => 'ready', ...$overrides,
        ]);
    }
}
