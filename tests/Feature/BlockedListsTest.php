<?php

namespace Tests\Feature;

use App\Models\AbuseReport;
use App\Models\BlockedListEntry;
use App\Models\User;
use App\Models\UserAuditEvent;
use App\Services\Installer\InstallState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockedListsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        app(InstallState::class)->lock();
    }

    public function test_blocked_lists_admin_page_renders_inside_shell(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.blocked-lists.index'))
            ->assertOk()
            ->assertSee('Blocked Lists')
            ->assertSee('Senders')
            ->assertSee('Manual rule')
            ->assertDontSee('This workspace is ready for implementation.');
    }

    public function test_manual_entry_is_normalized_and_duplicate_active_entries_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.blocked-lists.store'), $this->payload([
            'entry_type' => 'sender_email',
            'value' => ' Abuse@Example.COM ',
        ]))->assertRedirect();

        $entry = BlockedListEntry::query()->firstOrFail();
        $this->assertSame(hash('sha256', 'abuse@example.com'), $entry->normalized_hash);
        $this->assertSame('ab***@example.com', $entry->display_value);
        $this->assertSame('abuse@example.com', $entry->encrypted_normalized_value);

        $this->actingAs($admin)->from(route('admin.blocked-lists.index'))->post(route('admin.blocked-lists.store'), $this->payload([
            'entry_type' => 'sender_email',
            'value' => 'abuse@example.com',
        ]))->assertSessionHasErrors('value');

        $this->assertDatabaseCount('blocked_list_entries', 1);
    }

    public function test_wildcard_patterns_are_strictly_validated(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->from(route('admin.blocked-lists.index'))->post(route('admin.blocked-lists.store'), $this->payload([
            'entry_type' => 'recipient_email_pattern',
            'value' => '**@example.com',
        ]))->assertSessionHasErrors('value');

        $this->actingAs($admin)->post(route('admin.blocked-lists.store'), $this->payload([
            'entry_type' => 'recipient_email_pattern',
            'value' => '*@Example.com',
        ]))->assertRedirect();

        $this->assertDatabaseHas('blocked_list_entries', [
            'entry_type' => 'recipient_email_pattern',
            'normalized_hash' => hash('sha256', '*@example.com'),
        ]);
    }

    public function test_permissions_and_sensitive_ip_masking_are_enforced(): void
    {
        $member = User::factory()->create();
        $moderator = User::factory()->create(['role' => 'moderator', 'is_admin' => true]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($member)->get(route('admin.blocked-lists.index'))->assertForbidden();

        $this->actingAs($admin)->post(route('admin.blocked-lists.store'), $this->payload([
            'entry_type' => 'ip_address',
            'value' => '198.51.100.42',
            'reason' => 'Confirmed infrastructure abuse from a reviewed source.',
        ]))->assertRedirect();

        $this->actingAs($moderator)->get(route('admin.blocked-lists.index', ['group' => 'ip-rules']))
            ->assertOk()
            ->assertSee('198.51.***.***')
            ->assertDontSee('198.51.100.42');

        $this->actingAs($admin)->get(route('admin.blocked-lists.index', ['group' => 'ip-rules']))
            ->assertOk()
            ->assertSee('198.51.100.42');
    }

    public function test_update_toggle_related_case_and_audit_metadata_are_safe(): void
    {
        $admin = User::factory()->admin()->create();
        $case = $this->abuseReport();

        $this->actingAs($admin)->post(route('admin.blocked-lists.store'), $this->payload([
            'entry_type' => 'sender_domain',
            'value' => 'Bad-Example.test',
            'related_abuse_case' => $case->case_reference,
        ]))->assertRedirect();

        $entry = BlockedListEntry::query()->firstOrFail();

        $this->actingAs($admin)->put(route('admin.blocked-lists.update', $entry), $this->payload([
            'entry_type' => 'sender_domain',
            'value' => 'bad-example.test',
            'status' => 'inactive',
            'notes' => '<b>Reviewed</b> internal note',
        ]))->assertRedirect();

        $this->actingAs($admin)->post(route('admin.blocked-lists.activate', $entry))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.blocked-lists.deactivate', $entry))->assertRedirect();

        $this->assertSame('inactive', $entry->refresh()->status);
        $this->assertSame('Reviewed internal note', $entry->notes);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blocked_lists.entry_created', 'target_id' => $entry->id]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blocked_lists.entry_updated', 'target_id' => $entry->id]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blocked_lists.entry_activated', 'target_id' => $entry->id]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blocked_lists.entry_deactivated', 'target_id' => $entry->id]);
        $this->assertStringNotContainsString('Reviewed internal note', UserAuditEvent::query()->where('target_id', $entry->id)->pluck('metadata')->toJson());
    }

    public function test_abuse_blocklist_action_creates_owned_blocked_list_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $report = $this->abuseReport();

        $this->actingAs($admin)->post(route('admin.abuse-reports.operational-actions.execute', $report), [
            'operational_action' => 'block_sender_email',
            'block_value' => 'actor@example.net',
            'reason' => 'Confirmed sender abuse requires enforcement.',
            'confirm_action' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('blocked_list_entries', [
            'entry_type' => 'sender_email',
            'normalized_hash' => hash('sha256', 'actor@example.net'),
            'source' => 'abuse_report',
            'related_abuse_report_id' => $report->id,
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function payload(array $overrides = []): array
    {
        return [
            'entry_type' => 'sender_domain',
            'value' => 'example.test',
            'reason' => 'Reviewed manually by trust and safety operations.',
            'source' => 'manual',
            'status' => 'active',
            'starts_at' => now()->toDateString(),
            'expires_at' => now()->addMonth()->toDateString(),
            'notes' => null,
            ...$overrides,
        ];
    }

    private function abuseReport(): AbuseReport
    {
        return AbuseReport::query()->create([
            'case_reference' => 'AB-260613-'.str()->upper(str()->random(12)),
            'report_type' => 'spam',
            'priority' => 'high',
            'status' => 'reviewing',
            'reporter_name' => 'Reporter',
            'reporter_email' => 'reporter@example.test',
            'reporter_email_hash' => hash('sha256', 'reporter@example.test'),
            'subject' => 'Reviewed abuse source',
            'description' => 'Reviewed report content.',
            'description_excerpt' => 'Reviewed report content.',
            'reporter_notification_status' => 'ready',
        ]);
    }
}
