<?php

namespace Tests\Feature;

use App\Models\AbuseSignal;
use App\Models\SecuritySetting;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\UserAuditEvent;
use App\Services\Audit\AuditLogger;
use App\Services\Security\AbuseSignalAggregator;
use App\Services\Security\AbuseSignalService;
use App\Services\Security\SecuritySettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityDefenseCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_security_defense_center_renders_inside_admin_shell(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.security-defense-center.index'))
            ->assertOk()
            ->assertSee('Security Defense Center')
            ->assertSee('Cloudflare Turnstile')
            ->assertSee('Google reCAPTCHA')
            ->assertSee('Akismet comment spam')
            ->assertDontSee('This workspace is ready for implementation.');
    }

    public function test_bot_provider_secrets_are_encrypted_masked_and_not_audited(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.security-defense-center.bot.update'), [
                'provider' => 'turnstile',
                'recaptcha_mode' => 'v2_checkbox',
                'site_key' => 'site-secret-value',
                'secret_key' => 'provider-secret-value',
                'minimum_score' => 0.5,
                'fail_mode' => 'challenge',
                'is_active' => '1',
                'protected_forms' => ['login', 'comments'],
            ])
            ->assertRedirect(route('admin.security-defense-center.index'));

        $setting = SecuritySetting::query()->where('group', 'bot_protection')->firstOrFail();

        $this->assertStringNotContainsString('provider-secret-value', json_encode($setting->payload));
        $this->assertStringNotContainsString('provider-secret-value', $setting->encrypted_secrets);
        $this->assertSame('provider-secret-value', json_decode(Crypt::decryptString($setting->encrypted_secrets), true)['secret_key']);

        $this->actingAs($admin)
            ->get(route('admin.security-defense-center.index'))
            ->assertOk()
            ->assertSee('••••••••', false)
            ->assertDontSee('provider-secret-value');

        $auditPayloads = UserAuditEvent::query()->where('event', 'security.bot_protection_updated')->pluck('metadata')->map(fn ($metadata) => json_encode($metadata))->join("\n");
        $this->assertStringNotContainsString('provider-secret-value', $auditPayloads);
        $this->assertStringNotContainsString('site-secret-value', $auditPayloads);
    }

    public function test_provider_tests_fail_gracefully_without_exposing_secrets(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.security-defense-center.test'), ['target' => 'bot_protection'])
            ->assertRedirect(route('admin.security-defense-center.index'))
            ->assertSessionHas('test_status.status', 'ready');

        $this->actingAs($admin)
            ->put(route('admin.security-defense-center.akismet.update'), [
                'api_key' => '',
                'site_url' => 'https://temp-mail.example',
                'is_active' => '1',
                'protected_comments' => '1',
                'contact_form_readiness' => '1',
                'mode' => 'hold_suspicious',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.security-defense-center.test'), ['target' => 'akismet'])
            ->assertRedirect(route('admin.security-defense-center.index'))
            ->assertSessionHas('test_status.status', 'failed');

        $this->assertDatabaseHas('security_settings', ['group' => 'akismet', 'last_test_status' => 'failed']);
    }

    public function test_deactivation_preserves_configuration(): void
    {
        $admin = User::factory()->admin()->create();

        $this->saveBot($admin, ['is_active' => '1', 'secret_key' => 'persisted-secret']);
        $this->saveBot($admin, ['is_active' => '0', 'secret_key' => '']);

        $settings = app(SecuritySettingsStore::class)->group('bot_protection', true);

        $this->assertFalse($settings['is_active']);
        $this->assertSame('persisted-secret', $settings['secrets']['secret_key']);
    }

    public function test_security_updates_are_owner_admin_only_and_reveal_is_owner_only(): void
    {
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)
            ->put(route('admin.security-defense-center.bot.update'), [
                'provider' => 'turnstile',
                'recaptcha_mode' => 'v2_checkbox',
                'minimum_score' => 0.5,
                'fail_mode' => 'challenge',
                'protected_forms' => ['login'],
            ])
            ->assertForbidden();

        $this->saveBot($owner, ['site_key' => 'owner-site', 'secret_key' => 'owner-secret']);

        $this->actingAs($admin)
            ->get(route('admin.security-defense-center.secret.reveal', ['bot_protection', 'secret_key']))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('admin.security-defense-center.secret.reveal', ['bot_protection', 'secret_key']))
            ->assertOk()
            ->assertJson(['value' => 'owner-secret']);
    }

    public function test_akismet_settings_are_encrypted_and_audited_without_api_key(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.security-defense-center.akismet.update'), [
                'api_key' => 'akismet-secret-key',
                'site_url' => 'https://temp-mail.example',
                'is_active' => '1',
                'protected_comments' => '1',
                'contact_form_readiness' => '1',
                'mode' => 'trash_spam',
            ])
            ->assertRedirect();

        $setting = SecuritySetting::query()->where('group', 'akismet')->firstOrFail();

        $this->assertStringNotContainsString('akismet-secret-key', json_encode($setting->payload));
        $this->assertStringNotContainsString('akismet-secret-key', $setting->encrypted_secrets);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'security.akismet_updated', 'actor_id' => $admin->id]);

        $auditPayloads = UserAuditEvent::query()->where('event', 'security.akismet_updated')->pluck('metadata')->map(fn ($metadata) => json_encode($metadata))->join("\n");
        $this->assertStringNotContainsString('akismet-secret-key', $auditPayloads);
    }

    public function test_rate_limits_render_save_and_drive_laravel_limiters(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.security-defense-center.index'))
            ->assertOk()
            ->assertSee('Rate limit policies')
            ->assertSee('Login attempts')
            ->assertSee('Admin access security');

        $this->actingAs($admin)
            ->put(route('admin.security-defense-center.rate-limits.update'), [
                'policies' => [
                    'login' => [
                        'key' => 'login',
                        'max_attempts' => 2,
                        'window_minutes' => 3,
                        'cooldown_minutes' => 3,
                        'strategy' => 'per_ip',
                        'is_active' => '1',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.security-defense-center.index'));

        $limit = RateLimiter::limiter('login')(request()->merge(['email' => 'rate@example.test']));

        $this->assertSame(2, $limit->maxAttempts);
        $this->assertSame(180, $limit->decaySeconds);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'security.rate_limits_updated', 'actor_id' => $admin->id]);
    }

    public function test_invalid_zero_rate_limits_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.security-defense-center.index'))
            ->put(route('admin.security-defense-center.rate-limits.update'), [
                'policies' => [
                    'login' => [
                        'key' => 'login',
                        'max_attempts' => 0,
                        'window_minutes' => 0,
                        'cooldown_minutes' => 0,
                        'strategy' => 'per_ip',
                        'is_active' => '1',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.security-defense-center.index'))
            ->assertSessionHasErrors([
                'policies.login.max_attempts',
                'policies.login.window_minutes',
                'policies.login.cooldown_minutes',
            ]);
    }

    public function test_ip_access_and_admin_security_are_audited_without_session_tokens(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.security-defense-center.ip-access.update'), [
                'allowlist' => "203.0.113.10\n203.0.113.11",
                'blocklist' => '198.51.100.4',
                'temporary_block_ready' => '1',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->put(route('admin.security-defense-center.admin-access.update'), [
                'password_min_length' => 14,
                'password_letters' => '1',
                'password_numbers' => '1',
                'password_symbols' => '1',
                'require_email_verification' => '1',
                'admin_session_lifetime' => 90,
                'login_alerts' => '1',
                'admin_ip_allowlist_ready' => '1',
                'require_2fa_readiness' => '1',
                'critical_notifications_ready' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('security_settings', ['group' => 'ip_access']);
        $this->assertDatabaseHas('security_settings', ['group' => 'admin_access']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'security.ip_access_updated']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'security.admin_access_updated']);

        $auditPayloads = UserAuditEvent::query()
            ->whereIn('event', ['security.ip_access_updated', 'security.admin_access_updated'])
            ->pluck('metadata')
            ->map(fn ($metadata) => json_encode($metadata))
            ->join("\n");

        $this->assertStringNotContainsString('other-session-id', strtolower($auditPayloads));
        $this->assertStringNotContainsString('token', strtolower($auditPayloads));
    }

    public function test_force_logout_requires_permission_confirmation_and_does_not_render_session_tokens(): void
    {
        $admin = User::factory()->admin()->create();
        $editor = User::factory()->editor()->create();
        $member = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'other-session-id',
            'user_id' => $member->id,
            'ip_address' => '203.0.113.12',
            'user_agent' => 'Feature test',
            'payload' => 'encrypted-payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($editor)
            ->post(route('admin.security-defense-center.force-logout'), ['confirmation' => 'LOG OUT SESSIONS'])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.security-defense-center.force-logout'), ['confirmation' => 'wrong'])
            ->assertSessionHasErrors('confirmation');

        $this->actingAs($admin)
            ->post(route('admin.security-defense-center.force-logout'), ['confirmation' => 'LOG OUT SESSIONS'])
            ->assertRedirect(route('admin.security-defense-center.index'));

        $this->assertDatabaseMissing('sessions', ['id' => 'other-session-id']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'security.sessions_force_logged_out', 'actor_id' => $admin->id]);

        $this->actingAs($admin)
            ->get(route('admin.security-defense-center.index'))
            ->assertOk()
            ->assertSee('Force logout sessions')
            ->assertDontSee('other-session-id')
            ->assertDontSee('encrypted-payload');
    }

    public function test_normal_users_cannot_access_admin_and_suspended_users_cannot_login(): void
    {
        $member = User::factory()->create();
        $suspended = User::factory()->suspended()->create([
            'email' => 'blocked@example.test',
            'password' => 'Secure123!',
        ]);

        $this->actingAs($member)
            ->get(route('admin.security-defense-center.index'))
            ->assertForbidden();

        auth()->logout();

        $this->post(route('login.store'), [
            'email' => $suspended->email,
            'password' => 'Secure123!',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_security_operations_renders_inside_admin_shell(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.security-defense-center.index'))
            ->assertOk()
            ->assertSee('Abuse monitoring overview')
            ->assertSee('Open alerts')
            ->assertSee('Critical alerts')
            ->assertSee('Abuse signal queue')
            ->assertSee('Rate limit policies');
    }

    public function test_repeated_signals_are_grouped_and_private_content_is_removed(): void
    {
        $service = app(AbuseSignalService::class);

        $first = $service->record([
            'signal_type' => 'rate_limited_request',
            'source_module' => 'security',
            'target_reference' => 'login',
            'ip' => '203.0.113.55',
            'metadata' => [
                'route' => 'login.store',
                'message_body' => 'private mailbox message',
                'password' => 'secret-password',
                'token' => 'secret-token',
                'nested' => [
                    'content' => 'nested private content',
                    'safe_count' => 4,
                ],
            ],
        ]);

        $second = $service->record([
            'signal_type' => 'rate_limited_request',
            'source_module' => 'security',
            'target_reference' => 'login',
            'ip' => '203.0.113.55',
            'metadata' => ['route' => 'login.store'],
        ]);

        $this->assertTrue($first->is($second));
        $this->assertSame(2, $second->refresh()->occurrence_count);
        $this->assertNotSame('203.0.113.55', $second->ip_hash);
        $this->assertSame(64, strlen($second->ip_hash));
        $this->assertArrayNotHasKey('message_body', $second->metadata);
        $this->assertArrayNotHasKey('password', $second->metadata);
        $this->assertArrayNotHasKey('token', $second->metadata);
        $this->assertStringNotContainsString('private mailbox message', json_encode($second->metadata));
        $this->assertStringNotContainsString('nested private content', json_encode($second->metadata));
        $this->assertSame(4, $second->metadata['nested']['safe_count']);

        $emailTarget = $service->record([
            'signal_type' => 'unusual_api_failure',
            'target_reference' => 'private@example.test',
        ]);

        $this->assertStringStartsWith('ref:', $emailTarget->target_reference);
        $this->assertStringNotContainsString('private@example.test', $emailTarget->target_reference);
    }

    public function test_signal_filters_and_status_actions_are_permission_protected_and_audited(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator', 'is_admin' => true]);
        $member = User::factory()->create();
        $signal = app(AbuseSignalService::class)->record([
            'signal_type' => 'suspicious_comment',
            'severity' => 'high',
            'source_module' => 'comments',
            'target_reference' => 'comment:42',
        ]);

        $this->actingAs($moderator)
            ->get(route('admin.security-defense-center.index', [
                'severity' => 'high',
                'signal_type' => 'suspicious_comment',
                'source_module' => 'comments',
                'status' => 'open',
            ]))
            ->assertOk()
            ->assertSee('Suspicious Comment');

        $this->actingAs($member)
            ->patch(route('admin.security-defense-center.signals.status', $signal), ['status' => 'reviewing'])
            ->assertForbidden();

        $this->actingAs($moderator)
            ->patch(route('admin.security-defense-center.signals.status', $signal), ['status' => 'reviewing'])
            ->assertRedirect(route('admin.security-defense-center.index'));

        $this->assertSame('reviewing', $signal->refresh()->status);
        $this->assertDatabaseHas('user_audit_events', [
            'event' => 'security.abuse_signal_status_changed',
            'actor_id' => $moderator->id,
            'target_type' => AbuseSignal::class,
            'target_id' => $signal->id,
        ]);
    }

    public function test_critical_signal_notifies_active_owner_and_admin(): void
    {
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $moderator = User::factory()->create(['role' => 'moderator', 'is_admin' => true]);

        $signal = app(AbuseSignalService::class)->record([
            'signal_type' => 'failed_admin_login',
            'severity' => 'critical',
            'source_module' => 'auth',
            'target_reference' => 'admin-login',
            'ip' => '198.51.100.20',
        ]);

        $this->assertDatabaseHas('system_notifications', [
            'recipient_user_id' => $owner->id,
            'event_key' => 'critical_security_signal',
            'severity' => 'critical',
            'target_type' => AbuseSignal::class,
            'target_id' => $signal->id,
        ]);
        $this->assertDatabaseHas('system_notifications', [
            'recipient_user_id' => $admin->id,
            'event_key' => 'critical_security_signal',
        ]);
        $this->assertDatabaseMissing('system_notifications', [
            'recipient_user_id' => $moderator->id,
            'event_key' => 'critical_security_signal',
        ]);

        $payload = SystemNotification::query()->where('event_key', 'critical_security_signal')->pluck('message')->join("\n");
        $this->assertStringNotContainsString('198.51.100.20', $payload);
    }

    public function test_audit_aggregation_is_idempotent_for_the_same_source_event(): void
    {
        $admin = User::factory()->admin()->create();

        app(AuditLogger::class)->record('auth.login_failed', $admin, $admin, [
            'reason' => 'suspended',
        ], [
            'module' => 'auth',
            'action' => 'Login failed',
            'severity' => 'warning',
            'ip_address' => '192.0.2.25',
        ]);

        $aggregator = app(AbuseSignalAggregator::class);
        $aggregator->aggregateRecent();
        $aggregator->aggregateRecent();

        $signal = AbuseSignal::query()->where('signal_type', 'failed_admin_login')->firstOrFail();

        $this->assertSame(1, $signal->occurrence_count);
        $this->assertSame('critical', $signal->severity);
        $this->assertStringNotContainsString('192.0.2.25', json_encode($signal->toArray()));
    }

    /** @param array<string, mixed> $overrides */
    private function saveBot(User $user, array $overrides = []): void
    {
        $this->actingAs($user)
            ->put(route('admin.security-defense-center.bot.update'), [
                'provider' => 'turnstile',
                'recaptcha_mode' => 'v2_checkbox',
                'site_key' => $overrides['site_key'] ?? 'site-key',
                'secret_key' => $overrides['secret_key'] ?? 'secret-key',
                'minimum_score' => 0.5,
                'fail_mode' => 'challenge',
                'is_active' => $overrides['is_active'] ?? '1',
                'protected_forms' => ['login', 'comments'],
            ])
            ->assertRedirect(route('admin.security-defense-center.index'));
    }
}
