<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\InboundMailConnection;
use App\Models\User;
use App\Services\Mail\ImapConnectionTester;
use App\Services\Mail\InboundMailExtensionChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class InboundMailConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_inbound_mail_page_renders_inside_admin_shell_and_replaces_placeholder(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.imap-smtp.index'))
            ->assertOk()
            ->assertSee('Inbound Mail Operations')
            ->assertSee('PHP IMAP extension required')
            ->assertSee('No inbound connections yet')
            ->assertDontSee('The route, authorization boundary');
    }

    public function test_connection_requires_an_existing_domain_and_valid_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.imap-smtp.create'))
            ->post(route('admin.imap-smtp.store'), [
                'name' => 'Primary inbound',
                'domain_id' => 9999,
                'host' => 'not a host',
                'port' => 70000,
                'encryption' => 'unsafe',
                'username' => 'provider@example.test',
                'password' => '',
                'mailbox' => "INBOX\nInjected",
                'connection_timeout' => 1,
                'validate_certificate' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.imap-smtp.create'))
            ->assertSessionHasErrors(['domain_id', 'host', 'port', 'encryption', 'password', 'mailbox', 'connection_timeout']);
    }

    public function test_password_is_encrypted_at_rest_masked_in_ui_and_absent_from_audit(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);

        $this->actingAs($admin)
            ->post(route('admin.imap-smtp.store'), $this->payload($domain, [
                'password' => 'provider-secret-value',
            ]))
            ->assertRedirect();

        $connection = InboundMailConnection::query()->firstOrFail();
        $raw = DB::table('inbound_mail_connections')->where('id', $connection->id)->value('encrypted_password');

        $this->assertNotSame('provider-secret-value', $raw);
        $this->assertSame('provider-secret-value', Crypt::decryptString($raw));
        $this->assertSame('provider-secret-value', $connection->encrypted_password);

        $this->actingAs($admin)
            ->get(route('admin.imap-smtp.edit', $connection))
            ->assertOk()
            ->assertSee('A credential is stored securely')
            ->assertDontSee('provider-secret-value');

        $event = DB::table('user_audit_events')->where('event', 'inbound_mail.connection_created')->first();
        $this->assertNotNull($event);
        $this->assertStringNotContainsString('provider-secret-value', (string) $event->metadata);
    }

    public function test_blank_password_preserves_existing_encrypted_secret(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);
        $connection = $this->connection($domain, $admin, 'keep-this-secret');
        $rawBefore = DB::table('inbound_mail_connections')->where('id', $connection->id)->value('encrypted_password');

        $this->actingAs($admin)
            ->put(route('admin.imap-smtp.update', $connection), $this->payload($domain, [
                'name' => 'Updated inbound',
                'password' => '',
            ]))
            ->assertRedirect(route('admin.imap-smtp.edit', $connection));

        $rawAfter = DB::table('inbound_mail_connections')->where('id', $connection->id)->value('encrypted_password');
        $this->assertSame($rawBefore, $rawAfter);
        $this->assertSame('keep-this-secret', $connection->refresh()->encrypted_password);
    }

    public function test_missing_imap_extension_returns_clear_safe_failure(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);
        $connection = $this->connection($domain, $admin, 'extension-secret');

        $checker = Mockery::mock(InboundMailExtensionChecker::class);
        $checker->shouldReceive('check')->andReturn([
            'ready' => false,
            'extension' => 'imap',
            'message' => 'PHP IMAP is missing.',
        ]);
        $this->app->instance(InboundMailExtensionChecker::class, $checker);

        $this->actingAs($admin)
            ->post(route('admin.imap-smtp.test', $connection))
            ->assertRedirect(route('admin.imap-smtp.edit', $connection))
            ->assertSessionHas('error', 'PHP IMAP extension is required before this connection can be tested.');

        $connection->refresh();
        $this->assertSame('failed', $connection->status);
        $this->assertStringNotContainsString('extension-secret', json_encode($connection->last_test_result));
    }

    public function test_failed_connection_test_is_audited_notified_and_does_not_expose_secret(): void
    {
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);
        $connection = $this->connection($domain, $admin, 'do-not-expose-this');

        $tester = Mockery::mock(ImapConnectionTester::class);
        $tester->shouldReceive('test')->once()->andReturn([
            'status' => 'failed',
            'message' => 'Authentication or mailbox selection failed. Verify the credentials and folder name.',
            'checks' => [
                'dns' => ['status' => 'passed', 'message' => 'Host resolved.'],
                'socket' => ['status' => 'passed', 'message' => 'Socket connected.'],
                'authentication' => ['status' => 'failed', 'message' => 'Authentication failed.'],
                'mailbox' => ['status' => 'pending', 'message' => 'Mailbox not checked.'],
            ],
        ]);
        $this->app->instance(ImapConnectionTester::class, $tester);

        $this->actingAs($admin)
            ->post(route('admin.imap-smtp.test', $connection))
            ->assertRedirect(route('admin.imap-smtp.edit', $connection));

        $connection->refresh();
        $this->assertSame('failed', $connection->status);
        $this->assertCount(1, $connection->health_history);
        $this->assertDatabaseHas('user_audit_events', [
            'event' => 'inbound_mail.connection_tested',
            'actor_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('system_notifications', [
            'recipient_user_id' => $owner->id,
            'event_key' => 'inbound_mail_connection_failed',
            'target_id' => $connection->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.imap-smtp.edit', $connection))
            ->assertOk()
            ->assertSee('Authentication failed')
            ->assertDontSee('do-not-expose-this');
    }

    public function test_owner_and_admin_can_manage_connections_while_other_roles_cannot(): void
    {
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $moderator = User::factory()->create(['role' => 'moderator', 'is_admin' => true]);
        $domain = $this->domain($admin);

        $this->actingAs($owner)->get(route('admin.imap-smtp.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.imap-smtp.create'))->assertOk();
        $this->actingAs($moderator)->get(route('admin.imap-smtp.index'))->assertForbidden();
        $this->actingAs($moderator)->post(route('admin.imap-smtp.store'), $this->payload($domain))->assertForbidden();
    }

    private function domain(User $actor): Domain
    {
        return Domain::query()->create([
            'domain_name' => 'inbound.example',
            'display_name' => 'Inbound Example',
            'is_active' => true,
            'is_default' => true,
            'status' => 'ready',
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function payload(Domain $domain, array $overrides = []): array
    {
        return [
            'name' => 'Primary inbound',
            'domain_id' => $domain->id,
            'host' => 'imap.example.com',
            'port' => 993,
            'encryption' => 'ssl',
            'username' => 'provider@example.test',
            'password' => 'SafeInbound123!',
            'mailbox' => 'INBOX',
            'connection_timeout' => 15,
            'validate_certificate' => '1',
            'is_active' => '1',
            ...$overrides,
        ];
    }

    private function connection(Domain $domain, User $actor, string $password): InboundMailConnection
    {
        return InboundMailConnection::query()->create([
            'domain_id' => $domain->id,
            'name' => 'Primary inbound',
            'host' => 'imap.example.com',
            'port' => 993,
            'encryption' => 'ssl',
            'username' => 'provider@example.test',
            'encrypted_password' => $password,
            'mailbox' => 'INBOX',
            'connection_timeout' => 15,
            'validate_certificate' => true,
            'is_active' => true,
            'status' => 'not_tested',
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }
}
