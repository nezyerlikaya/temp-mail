<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\InboundMailConnection;
use App\Models\Mailbox;
use App\Models\MailboxDeliveryHealthCheck;
use App\Models\MailboxRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailboxRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_mailbox_rules_page_replaces_placeholder_and_shows_retention_and_health(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.mailbox-rules.index'))
            ->assertOk()->assertSee('Mailbox Rules')->assertSee('Retention preview')
            ->assertSee('Delivery health')->assertSee('Expired mailbox cleanup')
            ->assertDontSee('The route, authorization boundary');
    }

    public function test_rules_use_safe_numeric_limits_and_preserve_invalid_input(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->from(route('admin.mailbox-rules.index'))->put(route('admin.mailbox-rules.update'), [
            ...$this->validRules(), 'guest_lifetime_minutes' => 1, 'maximum_message_size_kb' => 999999,
        ])->assertRedirect(route('admin.mailbox-rules.index'))
            ->assertSessionHasErrors(['guest_lifetime_minutes', 'maximum_message_size_kb'])
            ->assertSessionHasInput('guest_lifetime_minutes', 1);

        $this->assertDatabaseCount('mailbox_rules', 0);
    }

    public function test_admin_can_update_rules_and_audit_contains_safe_configuration_metadata(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.mailbox-rules.update'), $this->validRules())
            ->assertRedirect()->assertSessionHas('status', 'Mailbox rules saved.');

        $this->assertDatabaseHas('mailbox_rules', [
            'guest_lifetime_minutes' => 720, 'attachment_policy' => 'metadata_only', 'auto_delete_expired' => true,
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'mailbox.rules_updated', 'actor_id' => $admin->id]);
    }

    public function test_retention_preview_explains_expiry_and_purge_timing(): void
    {
        $admin = User::factory()->admin()->create();
        MailboxRule::query()->create([...$this->validRules(), 'updated_by' => $admin->id]);

        $this->actingAs($admin)->get(route('admin.mailbox-rules.index'))
            ->assertOk()->assertSee('12 hour(s)')->assertSee('Expired mailboxes are purged 2 day(s) after expiry.');
    }

    public function test_cleanup_removes_only_expired_mailboxes_beyond_delay_and_is_audited(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);
        MailboxRule::query()->create([...$this->validRules(), 'expired_cleanup_delay_hours' => 24, 'updated_by' => $admin->id]);
        $active = $this->mailbox($domain, 'active@rules.example', 'active', now()->subDays(5));
        $eligible = $this->mailbox($domain, 'expired@rules.example', 'expired', now()->subDays(2));
        $recent = $this->mailbox($domain, 'recent@rules.example', 'expired', now()->subHours(2));

        $this->actingAs($admin)->post(route('admin.mailbox-rules.cleanup'), ['confirmation' => 'CLEAN EXPIRED'])
            ->assertRedirect()->assertSessionHas('status', '1 expired mailbox(es) removed.');

        $this->assertDatabaseHas('mailboxes', ['id' => $active->id, 'status' => 'active']);
        $this->assertDatabaseMissing('mailboxes', ['id' => $eligible->id]);
        $this->assertDatabaseHas('mailboxes', ['id' => $recent->id, 'status' => 'expired']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'mailbox.expired_cleanup_run', 'actor_id' => $admin->id]);
    }

    public function test_cleanup_requires_confirmation(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post(route('admin.mailbox-rules.cleanup'))->assertSessionHasErrors('confirmation');
    }

    public function test_delivery_health_reuses_domains_and_inbound_connections_and_records_history(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);
        InboundMailConnection::query()->create([
            'domain_id' => $domain->id, 'name' => 'Primary inbound', 'host' => 'imap.rules.example', 'port' => 993,
            'encryption' => 'ssl', 'username' => 'inbox', 'encrypted_password' => 'secret', 'mailbox' => 'INBOX',
            'connection_timeout' => 15, 'validate_certificate' => true, 'is_active' => true, 'status' => 'connected',
            'created_by' => $admin->id, 'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('admin.mailbox-rules.health'))->assertRedirect();

        $check = MailboxDeliveryHealthCheck::query()->firstOrFail();
        $this->assertSame('degraded', $check->status);
        $this->assertSame('healthy', collect($check->summary['cards'])->firstWhere('label', 'DNS readiness')['status']);
        $this->assertSame('healthy', collect($check->summary['cards'])->firstWhere('label', 'Inbound connections')['status']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'mailbox.delivery_health_checked']);
        $this->assertDatabaseHas('system_notifications', ['event_key' => 'mailbox_delivery_health_degraded']);
    }

    public function test_non_admin_cannot_update_cleanup_or_run_health_checks(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator', 'is_admin' => true]);

        $this->actingAs($moderator)->put(route('admin.mailbox-rules.update'), $this->validRules())->assertForbidden();
        $this->actingAs($moderator)->post(route('admin.mailbox-rules.cleanup'), ['confirmation' => 'CLEAN EXPIRED'])->assertForbidden();
        $this->actingAs($moderator)->post(route('admin.mailbox-rules.health'))->assertForbidden();
    }

    /** @return array<string, mixed> */
    private function validRules(): array
    {
        return [
            'guest_lifetime_minutes' => 720, 'registered_lifetime_minutes' => 10080,
            'premium_lifetime_minutes' => 43200, 'maximum_active_mailboxes' => 10,
            'maximum_messages_per_inbox' => 100, 'maximum_message_size_kb' => 10240,
            'attachment_policy' => 'metadata_only', 'auto_delete_expired' => '1',
            'expired_cleanup_delay_hours' => 48, 'inbox_refresh_rate_limit' => 30,
            'random_alias_length' => 12, 'random_alias_format' => 'alphanumeric',
        ];
    }

    private function domain(User $admin): Domain
    {
        return Domain::query()->create([
            'domain_name' => 'rules.example', 'display_name' => 'Rules Example', 'is_active' => true,
            'is_public' => true, 'status' => 'ready', 'created_by' => $admin->id, 'updated_by' => $admin->id,
        ]);
    }

    private function mailbox(Domain $domain, string $address, string $status, mixed $expiresAt): Mailbox
    {
        return Mailbox::query()->create([
            'domain_id' => $domain->id, 'address' => $address, 'local_part' => str($address)->before('@'),
            'mailbox_type' => 'guest', 'status' => $status, 'expires_at' => $expiresAt,
            'last_activity_at' => now(), 'message_count' => 0,
        ]);
    }
}
