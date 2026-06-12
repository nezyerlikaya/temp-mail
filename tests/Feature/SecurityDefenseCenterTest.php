<?php

namespace Tests\Feature;

use App\Models\SecuritySetting;
use App\Models\User;
use App\Models\UserAuditEvent;
use App\Services\Security\SecuritySettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
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
