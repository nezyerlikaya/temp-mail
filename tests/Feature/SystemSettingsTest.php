<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Settings\BrandAssetResolver;
use App\Services\Settings\LegalPageResolver;
use App\Services\Settings\SettingsResolver;
use App\Services\Settings\SettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
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

    public function test_settings_page_renders_inside_admin_shell_and_replaces_placeholder(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('System Settings Center')
            ->assertSee('General')
            ->assertSee('Abuse email')
            ->assertSee('Reset group to defaults')
            ->assertSee('Operations workspace')
            ->assertDontSee('The route, authorization boundary');
    }

    public function test_general_settings_require_accessible_abuse_email_validation(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->followingRedirects()
            ->from(route('admin.settings.index', ['group' => 'general']))
            ->put(route('admin.settings.general.update'), [
                'site_name' => '',
                'admin_email' => 'invalid',
                'support_email' => 'support@example.com',
                'abuse_email' => '',
                'default_language' => 'xx',
                'default_timezone' => 'Mars/Olympus',
                'date_format' => 'invalid',
                'time_format' => 'invalid',
            ])
            ->assertOk()
            ->assertSee('Please fix the highlighted fields.')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('role="alert"', false);
    }

    public function test_owner_can_persist_general_settings_and_change_is_audited(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->put(route('admin.settings.general.update'), [
                'site_name' => 'Private Inbox Cloud',
                'site_tagline' => 'Calm disposable email.',
                'admin_email' => 'admin@example.com',
                'support_email' => 'support@example.com',
                'abuse_email' => 'abuse@example.com',
                'default_language' => 'en',
                'default_timezone' => 'Europe/Istanbul',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
            ])
            ->assertRedirect(route('admin.settings.index', ['group' => 'general']));

        $this->assertSame('Private Inbox Cloud', app(SettingsResolver::class)->group('general')['site_name']);
        $this->assertDatabaseHas('system_settings', ['group' => 'general', 'updated_by' => $owner->id]);
        $this->assertDatabaseHas('user_audit_events', ['actor_id' => $owner->id, 'event' => 'system.settings_updated']);
    }

    public function test_editor_and_member_cannot_update_global_settings(): void
    {
        $editor = User::factory()->editor()->create();
        $member = User::factory()->create();
        $payload = [
            'site_name' => 'Blocked', 'site_tagline' => null, 'admin_email' => 'admin@example.com',
            'support_email' => 'support@example.com', 'abuse_email' => 'abuse@example.com',
            'default_language' => 'en', 'default_timezone' => 'UTC', 'date_format' => 'M j, Y', 'time_format' => 'H:i',
        ];

        $this->actingAs($editor)->put(route('admin.settings.general.update'), $payload)->assertForbidden();
        $this->actingAs($member)->put(route('admin.settings.general.update'), $payload)->assertForbidden();
        $this->assertDatabaseMissing('system_settings', ['group' => 'general']);
    }

    public function test_store_rejects_secret_setting_keys(): void
    {
        $owner = User::factory()->owner()->create();

        $this->expectException(InvalidArgumentException::class);
        app(SettingsStore::class)->put('general', ['api_key' => 'must-not-store'], $owner);
    }

    public function test_brand_and_legal_resolvers_work_without_media_or_page_tables(): void
    {
        $this->assertFalse(app(BrandAssetResolver::class)->assets()['logo']['connected']);
        $this->assertSame('TM wordmark', app(BrandAssetResolver::class)->assets()['logo']['fallback']);
        $this->assertFalse(app(LegalPageResolver::class)->pages()['privacy']['connected']);
    }

    public function test_maintenance_mode_blocks_public_home_but_never_locks_admin_routes(): void
    {
        $owner = User::factory()->owner()->create();
        app(SettingsStore::class)->put('maintenance', [
            'enabled' => true,
            'message' => 'Service maintenance is in progress.',
            'allowed_admin_ips' => [],
        ], $owner);

        $this->get(route('home'))
            ->assertStatus(503)
            ->assertSee('Service maintenance is in progress.')
            ->assertSee(route('login'), false);

        $this->get(route('login'))->assertOk();
        $this->actingAs($owner)->get(route('admin.settings.index', ['group' => 'maintenance']))->assertOk();
    }

    public function test_settings_page_does_not_absorb_unrelated_module_configuration(): void
    {
        $owner = User::factory()->owner()->create();
        $response = $this->actingAs($owner)->get(route('admin.settings.index'));

        $response->assertOk()
            ->assertDontSee('SMTP password')
            ->assertDontSee('Stripe secret')
            ->assertDontSee('Turnstile secret')
            ->assertDontSee('SEO title')
            ->assertDontSee('Custom CSS');
    }

    public function test_group_can_be_reset_to_safe_defaults(): void
    {
        $owner = User::factory()->owner()->create();
        app(SettingsStore::class)->put('brand', [
            'logo_media_id' => 12,
            'favicon_media_id' => null,
            'app_icon_media_id' => null,
            'public_site_name' => 'Changed',
            'footer_brand_text' => 'Changed',
        ], $owner);

        $this->actingAs($owner)
            ->delete(route('admin.settings.reset', 'brand'))
            ->assertRedirect(route('admin.settings.index', ['group' => 'brand']));

        $this->assertSame(config('app.name'), app(SettingsResolver::class)->group('brand')['public_site_name']);
        $this->assertDatabaseMissing('system_settings', ['group' => 'brand']);
    }
}
