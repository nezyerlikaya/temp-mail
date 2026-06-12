<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailboxOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_mailbox_operations_renders_inside_admin_shell_and_replaces_placeholder(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.mailbox-operations.index'))
            ->assertOk()->assertSee('Mailbox Operations')->assertSee('Active inboxes')
            ->assertSee('Inbox lifecycle queue')->assertDontSee('The route, authorization boundary');
    }

    public function test_mailboxes_can_be_searched_and_filtered(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin, 'ready.example');
        $otherDomain = $this->domain($admin, 'other.example');
        $this->mailbox($domain, ['address' => 'alpha@ready.example', 'local_part' => 'alpha', 'mailbox_type' => 'registered', 'user_id' => $admin->id]);
        $this->mailbox($otherDomain, ['address' => 'beta@other.example', 'local_part' => 'beta', 'status' => 'locked']);

        $this->actingAs($admin)->get(route('admin.mailbox-operations.index', [
            'q' => 'alpha@', 'status' => 'active', 'domain_id' => $domain->id,
            'owner' => 'registered', 'mailbox_type' => 'registered', 'created' => 'today',
        ]))->assertOk()->assertSee('alpha@ready.example')->assertDontSee('beta@other.example');
    }

    public function test_mailbox_listing_is_paginated(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);
        foreach (range(1, 16) as $index) {
            $this->mailbox($domain, ['address' => 'box'.$index.'@ready.example', 'local_part' => 'box'.$index]);
        }

        $this->actingAs($admin)->get(route('admin.mailbox-operations.index'))
            ->assertOk()->assertSee('Next')->assertSee('15 of 16');
    }

    public function test_inactive_private_or_unready_domains_cannot_create_mailboxes(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin, 'blocked.example', ['is_active' => false, 'is_public' => false, 'status' => 'pending_dns']);

        $this->actingAs($admin)->from(route('admin.mailbox-operations.create'))->post(route('admin.mailbox-operations.store'), [
            'local_part' => 'blocked', 'domain_id' => $domain->id, 'mailbox_type' => 'guest',
        ])->assertRedirect(route('admin.mailbox-operations.create'))->assertSessionHasErrors('domain_id');

        $this->assertDatabaseMissing('mailboxes', ['address' => 'blocked@blocked.example']);
    }

    public function test_creation_normalizes_address_hashes_ip_and_records_safe_audit(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);

        $this->actingAs($admin)->withServerVariables(['REMOTE_ADDR' => '203.0.113.42'])->post(route('admin.mailbox-operations.store'), [
            'local_part' => 'Temp.Box', 'domain_id' => $domain->id, 'mailbox_type' => 'guest',
        ])->assertRedirect();

        $mailbox = Mailbox::query()->firstOrFail();
        $this->assertSame('temp.box@ready.example', $mailbox->address);
        $this->assertNotSame('203.0.113.42', $mailbox->created_ip_hash);
        $this->assertSame(64, strlen((string) $mailbox->created_ip_hash));
        $this->assertDatabaseHas('user_audit_events', ['event' => 'mailbox.created', 'actor_id' => $admin->id]);
    }

    public function test_private_message_content_is_absent_from_listings(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);
        $this->mailbox($domain, ['activity_timeline' => [[
            'event' => 'message_received', 'label' => 'Message received',
            'detail' => 'PRIVATE MESSAGE BODY MUST NEVER RENDER', 'occurred_at' => now()->toIso8601String(),
        ]]]);

        $this->actingAs($admin)->get(route('admin.mailbox-operations.index'))
            ->assertOk()->assertDontSee('PRIVATE MESSAGE BODY MUST NEVER RENDER');
    }

    public function test_detail_foundation_and_permissions_are_enforced(): void
    {
        $admin = User::factory()->admin()->create();
        $moderator = User::factory()->create(['role' => 'moderator', 'is_admin' => true]);
        $member = User::factory()->create();
        $mailbox = $this->mailbox($this->domain($admin));

        $this->actingAs($moderator)->get(route('admin.mailbox-operations.show', $mailbox))
            ->assertOk()->assertSee('Mailbox summary')->assertSee('Activity timeline');
        $this->actingAs($moderator)->get(route('admin.mailbox-operations.create'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.mailbox-operations.index'))->assertForbidden();
    }

    /** @param array<string, mixed> $overrides */
    private function domain(User $actor, string $name = 'ready.example', array $overrides = []): Domain
    {
        return Domain::query()->create([
            'domain_name' => $name, 'display_name' => $name, 'is_active' => true, 'is_public' => true,
            'status' => 'ready', 'created_by' => $actor->id, 'updated_by' => $actor->id, ...$overrides,
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function mailbox(Domain $domain, array $overrides = []): Mailbox
    {
        return Mailbox::query()->create([
            'domain_id' => $domain->id, 'address' => 'inbox@'.$domain->domain_name, 'local_part' => 'inbox',
            'mailbox_type' => 'guest', 'status' => 'active', 'last_activity_at' => now(), 'message_count' => 0,
            'activity_timeline' => [['event' => 'mailbox_created', 'label' => 'Mailbox created', 'detail' => 'Lifecycle started.', 'occurred_at' => now()->toIso8601String()]],
            ...$overrides,
        ]);
    }
}
