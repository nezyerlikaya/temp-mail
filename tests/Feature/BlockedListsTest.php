<?php

namespace Tests\Feature;

use App\Models\AbuseReport;
use App\Models\BlockedListEntry;
use App\Models\BlogPost;
use App\Models\Domain;
use App\Models\Locale;
use App\Models\User;
use App\Models\UserAuditEvent;
use App\Services\BlockedLists\BlockedEntryExpiryService;
use App\Services\BlockedLists\BlockedListEnforcementService;
use App\Services\BlockedLists\BlockedListMatcher;
use App\Services\Comments\CommentStore;
use App\Services\Installer\InstallState;
use App\Services\Mailboxes\MailboxStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
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

    public function test_matcher_enforces_only_active_unexpired_rules(): void
    {
        $admin = User::factory()->admin()->create();

        $this->createEntry($admin, 'sender_email', 'active@example.com', 'active');
        $this->createEntry($admin, 'sender_email', 'inactive@example.com', 'inactive');
        $this->createEntry($admin, 'sender_email', 'expired@example.com', 'active', now()->subDay());

        app(BlockedEntryExpiryService::class)->expire($admin);

        $matcher = app(BlockedListMatcher::class);

        $this->assertSame('blocked', $matcher->match('sender_email', 'active@example.com')['decision']);
        $this->assertSame('inactive_rule_ignored', $matcher->match('sender_email', 'inactive@example.com')['decision']);
        $this->assertSame('expired_rule_ignored', $matcher->match('sender_email', 'expired@example.com')['decision']);
        $this->assertSame('allowed', $matcher->match('sender_email', 'clear@example.com')['decision']);
    }

    public function test_enforcement_hooks_block_mailbox_creation_and_comments_without_logging_private_body(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);
        $post = $this->blogPost($admin);

        $this->createEntry($admin, 'recipient_email_pattern', 'blocked*@example.test');
        $this->createEntry($admin, 'blocked_phrase', 'private banned phrase');

        try {
            app(MailboxStore::class)->create(['local_part' => 'blocked-one', 'mailbox_type' => 'guest'], $domain, $admin, '198.51.100.9');
            $this->fail('Mailbox creation should have been blocked.');
        } catch (ValidationException $exception) {
            $this->assertSame('This mailbox address or request source is blocked by a reviewed rule.', $exception->errors()['local_part'][0]);
        }

        try {
            app(CommentStore::class)->create($post, [
                'author_name' => 'Reader',
                'author_email' => 'reader@example.com',
                'content' => 'This contains private banned phrase and confidential words.',
            ], request());
        } catch (\Throwable $exception) {
            $this->fail($exception->getMessage());
        }

        $this->assertDatabaseHas('comments', ['status' => 'spam', 'manual_override' => 'blocklist']);
        $this->assertStringNotContainsString('confidential words', UserAuditEvent::query()->pluck('metadata')->toJson());
    }

    public function test_csv_import_preview_transaction_export_and_permissions_are_safe(): void
    {
        $admin = User::factory()->admin()->create();
        $moderator = User::factory()->create(['role' => 'moderator', 'is_admin' => true]);
        $csv = "entry_type,value,reason,source,status,expires_at\nsender_domain,bad.test,Reviewed import source,manual,active,\nip_address,198.51.100.44,Reviewed IP source,manual,active,";

        $this->actingAs($moderator)->post(route('admin.blocked-lists.import'), ['mode' => 'preview', 'csv' => $csv])->assertForbidden();

        $this->actingAs($admin)->post(route('admin.blocked-lists.import'), ['mode' => 'preview', 'csv' => $csv])
            ->assertRedirect()
            ->assertSessionHas('blocked_import_preview');

        $this->actingAs($admin)->post(route('admin.blocked-lists.import'), ['mode' => 'import', 'csv' => $csv])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('blocked_list_entries', ['entry_type' => 'sender_domain', 'normalized_hash' => hash('sha256', 'bad.test')]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blocked_lists.imported']);

        $badCsv = "entry_type,value,reason,source,status,expires_at\nunknown,bad.test,Reviewed import source,manual,active,";
        $this->actingAs($admin)->from(route('admin.blocked-lists.index'))->post(route('admin.blocked-lists.import'), ['mode' => 'import', 'csv' => $badCsv])
            ->assertSessionHasErrors('csv');

        $this->actingAs($moderator)->get(route('admin.blocked-lists.export'))->assertForbidden();
        $export = $this->actingAs($admin)->get(route('admin.blocked-lists.export', ['status' => 'active', 'include_sensitive_ip' => 1]));
        $export->assertOk();
        $this->assertStringContainsString('198.51.100.44', $export->streamedContent());
    }

    public function test_bulk_changes_and_enforcement_test_panel_routes_work(): void
    {
        $admin = User::factory()->admin()->create();
        $entry = $this->createEntry($admin, 'sender_domain', 'bulk.test');

        $this->actingAs($admin)->post(route('admin.blocked-lists.test'), [
            'entry_type' => 'sender_domain',
            'value' => 'bulk.test',
        ])->assertRedirect()->assertSessionHas('blocked_match_result');

        $this->actingAs($admin)->post(route('admin.blocked-lists.bulk'), [
            'entry_ids' => [$entry->id],
            'bulk_action' => 'deactivate',
        ])->assertRedirect();

        $this->assertSame('inactive', $entry->refresh()->status);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blocked_lists.bulk_updated']);
    }

    public function test_security_and_api_ip_decision_readiness_use_central_enforcement(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createEntry($admin, 'ip_address', '203.0.113.0/24');

        $service = app(BlockedListEnforcementService::class);

        $this->assertSame('blocked', $service->securityIp('203.0.113.10')['decision']);
        $this->assertSame('blocked', $service->apiIpDecision('203.0.113.10')['decision']);
        $this->assertSame('allowed', $service->apiIpDecision('198.51.100.10')['decision']);
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

    private function createEntry(User $actor, string $type, string $value, string $status = 'active', $expiresAt = null): BlockedListEntry
    {
        $this->actingAs($actor)->post(route('admin.blocked-lists.store'), $this->payload([
            'entry_type' => $type,
            'value' => $value,
            'status' => $status,
            'starts_at' => $expiresAt?->isPast() ? now()->subMonth()->toDateString() : now()->toDateString(),
            'expires_at' => $expiresAt?->toDateString() ?? now()->addMonth()->toDateString(),
        ]))->assertRedirect();

        return BlockedListEntry::query()->latest('id')->firstOrFail();
    }

    private function domain(User $admin): Domain
    {
        return Domain::query()->create([
            'domain_name' => 'example.test',
            'display_name' => 'Example',
            'is_active' => true,
            'is_public' => true,
            'catch_all_ready' => true,
            'is_default' => true,
            'status' => 'active',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
    }

    private function blogPost(User $admin): BlogPost
    {
        $locale = Locale::query()->create([
            'locale' => 'en',
            'language_name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'region' => 'Global',
            'is_active' => true,
            'is_default' => true,
            'launch_status' => 'published',
            'market_readiness' => 'ready',
        ]);

        return BlogPost::query()->create([
            'locale_id' => $locale->id,
            'title' => 'Privacy note',
            'slug' => 'privacy-note',
            'content' => 'Body',
            'status' => 'published',
            'author_id' => $admin->id,
            'comments_enabled' => true,
            'comments_moderation_required' => true,
        ]);
    }
}
