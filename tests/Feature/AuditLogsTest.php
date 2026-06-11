<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAuditEvent;
use App\Services\Audit\AuditLogger;
use App\Services\Audit\AuditSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogsTest extends TestCase
{
    use RefreshDatabase;

    private string $recoveryPath;

    private ?string $originalRecovery = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->recoveryPath = storage_path('app/installer-recovery.flag');
        $this->originalRecovery = file_exists($this->recoveryPath) ? file_get_contents($this->recoveryPath) : null;

        if (file_exists($this->recoveryPath)) {
            unlink($this->recoveryPath);
        }
    }

    protected function tearDown(): void
    {
        if ($this->originalRecovery !== null) {
            file_put_contents($this->recoveryPath, $this->originalRecovery);
        } elseif (file_exists($this->recoveryPath)) {
            unlink($this->recoveryPath);
        }

        parent::tearDown();
    }

    public function test_audit_logs_page_renders_inside_admin_shell_and_replaces_placeholder(): void
    {
        $owner = User::factory()->owner()->create();
        app(AuditLogger::class)->record('user.role_changed', $owner, $owner, ['new_role' => 'owner']);

        $this->actingAs($owner)
            ->get(route('admin.activity-audit-logs.index'))
            ->assertOk()
            ->assertSee('Activity & Audit Logs')
            ->assertSee('Audit feed')
            ->assertSee('Role Changed')
            ->assertSee('Operations workspace')
            ->assertDontSee('The route, authorization boundary');
    }

    public function test_normal_users_cannot_view_audit_logs(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('admin.activity-audit-logs.index'))
            ->assertForbidden();
    }

    public function test_moderator_has_limited_view_readiness(): void
    {
        $moderator = User::factory()->admin()->create(['role' => 'moderator']);

        $this->actingAs($moderator)
            ->get(route('admin.activity-audit-logs.index'))
            ->assertOk()
            ->assertSee('Activity & Audit Logs');
    }

    public function test_audit_logger_masks_sensitive_metadata_before_storage(): void
    {
        $owner = User::factory()->owner()->create();

        app(AuditLogger::class)->record('system.settings_updated', $owner, $owner, [
            'smtp_password' => 'super-secret',
            'database_password' => 'db-secret',
            'nested' => ['api_key' => 'key-secret', 'safe' => 'visible'],
        ]);

        $event = UserAuditEvent::query()->firstOrFail();

        $this->assertSame('[masked]', $event->metadata['smtp_password']);
        $this->assertSame('[masked]', $event->metadata['database_password']);
        $this->assertSame('[masked]', $event->metadata['nested']['api_key']);
        $this->assertSame('visible', $event->metadata['nested']['safe']);

        $this->actingAs($owner)
            ->get(route('admin.activity-audit-logs.index'))
            ->assertOk()
            ->assertDontSee('super-secret')
            ->assertDontSee('db-secret')
            ->assertDontSee('key-secret')
            ->assertSee('[masked]');
    }

    public function test_audit_feed_filters_by_module_actor_action_severity_and_date(): void
    {
        $owner = User::factory()->owner()->create(['name' => 'Ada Owner', 'email' => 'ada@example.com']);
        $admin = User::factory()->admin()->create(['name' => 'Grace Admin', 'email' => 'grace@example.com']);

        app(AuditLogger::class)->record('system.settings_updated', $owner, $owner, [], ['severity' => 'critical']);
        app(AuditLogger::class)->record('auth.login_success', $admin, $admin, [], ['module' => 'auth', 'action' => 'Login success']);

        $this->actingAs($owner)
            ->get(route('admin.activity-audit-logs.index', [
                'module' => 'system',
                'actor' => 'ada@example.com',
                'action' => 'Settings Updated',
                'severity' => 'critical',
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Settings Updated')
            ->assertDontSee('auth.login_success');
    }

    public function test_failed_login_and_logout_are_audited_with_nullable_actor_readiness(): void
    {
        $owner = User::factory()->owner()->create([
            'email' => 'owner@example.com',
            'password' => 'Secure123!',
        ]);

        $this->post(route('login.store'), [
            'email' => 'missing@example.com',
            'password' => 'bad-password',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseHas('user_audit_events', [
            'actor_id' => null,
            'event' => 'auth.login_failed',
            'severity' => 'warning',
        ]);

        $this->post(route('login.store'), [
            'email' => $owner->email,
            'password' => 'Secure123!',
        ])->assertRedirect(route('dashboard'));

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertDatabaseHas('user_audit_events', ['actor_id' => $owner->id, 'event' => 'auth.login_success']);
        $this->assertDatabaseHas('user_audit_events', ['actor_id' => $owner->id, 'event' => 'auth.logout']);
    }

    public function test_filter_validation_errors_render_error_summary(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->followingRedirects()
            ->from(route('admin.activity-audit-logs.index'))
            ->get(route('admin.activity-audit-logs.index', ['severity' => 'panic']))
            ->assertOk()
            ->assertSee('Please fix the highlighted fields.')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('role="alert"', false);
    }

    public function test_sensitive_masking_list_is_centralized(): void
    {
        $this->assertSame([
            'password',
            'secret',
            'token',
            'api_key',
            'smtp_password',
            'database_password',
        ], app(AuditSanitizer::class)->sensitiveKeys());
    }
}
