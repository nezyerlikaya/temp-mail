<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\InboundMailConnection;
use App\Models\SmtpConnection;
use App\Models\User;
use App\Services\Domains\DomainDnsCheckService;
use App\Services\EmailTemplates\EmailTemplateDeliverabilityService;
use App\Services\Mail\SmtpConnectionTester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class SmtpMailInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_imap_smtp_page_renders_smtp_and_combined_health_inside_admin_shell(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.imap-smtp.index'))
            ->assertOk()
            ->assertSee('IMAP / SMTP Operations')
            ->assertSee('Mail infrastructure health')
            ->assertSee('Outbound SMTP')
            ->assertSee('No SMTP connections yet')
            ->assertDontSee('The route, authorization boundary');
    }

    public function test_smtp_credentials_are_encrypted_masked_and_not_audited(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);

        $this->actingAs($admin)
            ->post(route('admin.imap-smtp.smtp.store'), $this->payload($domain, ['password' => 'smtp-secret-value']))
            ->assertRedirect();

        $connection = SmtpConnection::query()->firstOrFail();
        $raw = DB::table('smtp_connections')->where('id', $connection->id)->value('encrypted_password');

        $this->assertNotSame('smtp-secret-value', $raw);
        $this->assertSame('smtp-secret-value', Crypt::decryptString($raw));
        $this->assertSame('smtp-secret-value', $connection->encrypted_password);
        $this->assertTrue($connection->is_default);

        $this->actingAs($admin)
            ->get(route('admin.imap-smtp.smtp.edit', $connection))
            ->assertOk()
            ->assertSee('A credential is stored securely')
            ->assertDontSee('smtp-secret-value');

        $event = DB::table('user_audit_events')->where('event', 'smtp.connection_created')->first();
        $this->assertNotNull($event);
        $this->assertStringNotContainsString('smtp-secret-value', (string) $event->metadata);
    }

    public function test_blank_smtp_password_preserves_existing_encrypted_secret(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);
        $connection = $this->smtp($domain, $admin, 'keep-smtp-secret');
        $rawBefore = DB::table('smtp_connections')->where('id', $connection->id)->value('encrypted_password');

        $this->actingAs($admin)
            ->put(route('admin.imap-smtp.smtp.update', $connection), $this->payload($domain, [
                'name' => 'Updated SMTP',
                'password' => '',
            ]))
            ->assertRedirect(route('admin.imap-smtp.smtp.edit', $connection));

        $rawAfter = DB::table('smtp_connections')->where('id', $connection->id)->value('encrypted_password');
        $this->assertSame($rawBefore, $rawAfter);
        $this->assertSame('keep-smtp-secret', $connection->refresh()->encrypted_password);
    }

    public function test_only_one_default_smtp_connection_is_allowed(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);
        $first = $this->smtp($domain, $admin, 'first-secret', ['name' => 'First SMTP', 'is_default' => true]);
        $second = $this->smtp($domain, $admin, 'second-secret', ['name' => 'Second SMTP', 'from_email' => 'second@example.com']);

        $this->actingAs($admin)
            ->post(route('admin.imap-smtp.smtp.default', $second))
            ->assertRedirect(route('admin.imap-smtp.index'));

        $this->assertFalse($first->refresh()->is_default);
        $this->assertTrue($second->refresh()->is_default);

        $this->actingAs($admin)
            ->patch(route('admin.imap-smtp.smtp.status', $second), ['status_action' => 'deactivate'])
            ->assertSessionHasErrors('smtp');
    }

    public function test_failed_smtp_test_is_audited_notified_and_does_not_expose_secret(): void
    {
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);
        $connection = $this->smtp($domain, $admin, 'hidden-smtp-secret');

        $tester = Mockery::mock(SmtpConnectionTester::class);
        $tester->shouldReceive('test')->once()->andReturn([
            'status' => 'failed',
            'message' => 'SMTP authentication failed. Verify the username and password.',
            'checks' => [
                'dns' => ['status' => 'passed', 'message' => 'Host resolved.'],
                'socket' => ['status' => 'passed', 'message' => 'Socket connected.'],
                'authentication' => ['status' => 'failed', 'message' => 'The SMTP server rejected authentication.'],
                'delivery_readiness' => ['status' => 'pending', 'message' => 'Waiting for authentication.'],
            ],
        ]);
        $this->app->instance(SmtpConnectionTester::class, $tester);

        $this->actingAs($admin)
            ->post(route('admin.imap-smtp.smtp.test', $connection))
            ->assertRedirect(route('admin.imap-smtp.smtp.edit', $connection))
            ->assertSessionHas('error', 'SMTP authentication failed. Verify the username and password.');

        $connection->refresh();
        $this->assertSame('failed', $connection->status);
        $this->assertStringNotContainsString('hidden-smtp-secret', json_encode($connection->last_test_result));
        $this->assertDatabaseHas('user_audit_events', ['event' => 'smtp.connection_tested', 'actor_id' => $admin->id]);
        $this->assertDatabaseHas('system_notifications', [
            'recipient_user_id' => $owner->id,
            'event_key' => 'smtp_connection_failed',
            'target_id' => $connection->id,
        ]);
    }

    public function test_combined_health_and_email_template_readiness_use_default_smtp(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);
        $this->smtp($domain, $admin, 'ready-secret', ['status' => 'connected', 'is_default' => true]);
        InboundMailConnection::query()->create([
            'domain_id' => $domain->id,
            'name' => 'Inbound',
            'host' => 'imap.example.com',
            'port' => 993,
            'encryption' => 'ssl',
            'username' => 'inbound@example.com',
            'encrypted_password' => 'inbound-secret',
            'mailbox' => 'INBOX',
            'connection_timeout' => 15,
            'validate_certificate' => true,
            'is_active' => true,
            'status' => 'connected',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.imap-smtp.index'))
            ->assertOk()
            ->assertSee('Outbound SMTP health')
            ->assertSee('1/1 connected');

        $readiness = app(EmailTemplateDeliverabilityService::class)->readiness();
        $this->assertTrue($readiness['ready']);
        $this->assertStringContainsString('default SMTP connection', $readiness['message']);
    }

    public function test_run_all_checks_executes_active_smtp_tests_and_records_health_audit(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain($admin);
        $connection = $this->smtp($domain, $admin, 'run-all-secret', ['is_active' => true]);

        $tester = Mockery::mock(SmtpConnectionTester::class);
        $tester->shouldReceive('test')->once()->with(Mockery::on(fn (SmtpConnection $smtp): bool => $smtp->is($connection)))->andReturn([
            'status' => 'connected',
            'message' => 'SMTP connection is ready.',
            'checks' => [
                'dns' => ['status' => 'passed', 'message' => 'Host resolved.'],
                'socket' => ['status' => 'passed', 'message' => 'Socket connected.'],
                'authentication' => ['status' => 'passed', 'message' => 'Authentication succeeded.'],
                'delivery_readiness' => ['status' => 'passed', 'message' => 'Ready.'],
            ],
        ]);
        $this->app->instance(SmtpConnectionTester::class, $tester);

        $dns = Mockery::mock(DomainDnsCheckService::class);
        $dns->shouldReceive('check')->once()->andReturn([
            'mx' => ['status' => 'ready', 'message' => 'MX ready.'],
            'spf' => ['status' => 'ready', 'message' => 'SPF ready.'],
        ]);
        $this->app->instance(DomainDnsCheckService::class, $dns);

        $this->actingAs($admin)
            ->post(route('admin.imap-smtp.run-all-checks'))
            ->assertRedirect(route('admin.imap-smtp.index'));

        $this->assertSame('connected', $connection->refresh()->status);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'mail_infrastructure.checks_run', 'actor_id' => $admin->id]);
    }

    private function domain(User $actor): Domain
    {
        return Domain::query()->create([
            'domain_name' => 'example.com',
            'display_name' => 'Example',
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
            'name' => 'Primary SMTP',
            'domain_id' => $domain->id,
            'host' => 'smtp.example.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'smtp@example.com',
            'password' => 'SafeSmtp123!',
            'from_email' => 'noreply@example.com',
            'from_name' => 'Temp Mail SaaS',
            'reply_to_email' => 'support@example.com',
            'connection_timeout' => 15,
            'validate_certificate' => '1',
            'is_active' => '1',
            'is_default' => '1',
            ...$overrides,
        ];
    }

    /** @param array<string, mixed> $overrides */
    private function smtp(Domain $domain, User $actor, string $password, array $overrides = []): SmtpConnection
    {
        return SmtpConnection::query()->create([
            'domain_id' => $domain->id,
            'name' => 'Primary SMTP',
            'host' => 'smtp.example.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'smtp@example.com',
            'encrypted_password' => $password,
            'from_email' => 'noreply@example.com',
            'from_name' => 'Temp Mail SaaS',
            'reply_to_email' => 'support@example.com',
            'reply_to_ready' => true,
            'connection_timeout' => 15,
            'validate_certificate' => true,
            'is_active' => true,
            'is_default' => false,
            'status' => 'not_tested',
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
            ...$overrides,
        ]);
    }
}
