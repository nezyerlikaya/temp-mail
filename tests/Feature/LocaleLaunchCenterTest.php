<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\User;
use App\Services\Localization\LocaleSettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleLaunchCenterTest extends TestCase
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

    public function test_locale_launch_center_renders_inside_admin_shell_and_replaces_placeholder(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.locale-launch-center.index'))
            ->assertOk()
            ->assertSee('Locale Launch Center')
            ->assertSee('Market readiness queue')
            ->assertSee('Launch queue')
            ->assertSee('Total locales')
            ->assertSee('Avg. text coverage')
            ->assertSee('Copy &amp; UI Text coverage', false)
            ->assertSee('Open Translation Center')
            ->assertSee('Open SEO Growth Center')
            ->assertSee('role="status"', false)
            ->assertSee('Operations workspace')
            ->assertDontSee('The route, authorization boundary')
            ->assertDontSee('<textarea', false)
            ->assertDontSee('name="homepage', false);
    }

    public function test_foundation_seeds_thirty_priority_locales_with_english_default_and_rtl_support(): void
    {
        app(LocaleSettingsStore::class)->ensureSeeded();

        $this->assertSame(30, Locale::query()->count());
        $this->assertDatabaseHas('locales', ['locale' => 'en', 'is_active' => true, 'is_default' => true, 'direction' => 'ltr']);
        $this->assertDatabaseHas('locales', ['locale' => 'he', 'direction' => 'rtl']);
        $this->assertDatabaseHas('locales', ['locale' => 'ar', 'direction' => 'rtl']);
    }

    public function test_default_locale_must_be_active_and_unique(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();

        $this->actingAs($admin)
            ->from(route('admin.locale-launch-center.index'))
            ->put(route('admin.locale-launch-center.update'), [
                'locales' => [
                    'en' => [
                        'market_readiness' => 'ready',
                        'is_default' => '1',
                        'sort_order' => 1,
                        'launch_status' => 'launched',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.locale-launch-center.index'))
            ->assertSessionHasErrors('locales');

        $this->actingAs($admin)
            ->from(route('admin.locale-launch-center.index'))
            ->put(route('admin.locale-launch-center.update'), [
                'locales' => [
                    'en' => [
                        'market_readiness' => 'ready',
                        'is_active' => '1',
                        'is_default' => '1',
                        'sort_order' => 1,
                        'launch_status' => 'launched',
                    ],
                    'de' => [
                        'market_readiness' => 'ready',
                        'is_active' => '1',
                        'is_default' => '1',
                        'sort_order' => 2,
                        'launch_status' => 'ready',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.locale-launch-center.index'))
            ->assertSessionHasErrors('locales');
    }

    public function test_admin_can_change_default_locale_and_previous_default_is_cleared(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();

        $this->actingAs($admin)
            ->put(route('admin.locale-launch-center.update'), [
                'locales' => [
                    'de' => [
                        'market_readiness' => 'ready',
                        'is_active' => '1',
                        'is_default' => '1',
                        'sort_order' => 2,
                        'launch_status' => 'launched',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.locale-launch-center.index'));

        $this->assertDatabaseHas('locales', ['locale' => 'de', 'is_active' => true, 'is_default' => true]);
        $this->assertDatabaseHas('locales', ['locale' => 'en', 'is_default' => false]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'locale.settings_updated', 'actor_id' => $admin->id]);
    }

    public function test_search_filter_and_view_modes_are_supported(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.locale-launch-center.index', ['q' => 'Hebrew', 'direction' => 'rtl', 'per_page' => 30]))
            ->assertOk()
            ->assertSee('Hebrew')
            ->assertSee('עברית')
            ->assertSee('RTL');
    }

    public function test_readiness_filter_and_market_queue_sections_render(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.locale-launch-center.index', ['readiness' => 'high', 'per_page' => 20]))
            ->assertOk()
            ->assertSee('Ready for launch')
            ->assertSee('Missing SEO metadata')
            ->assertSee('Missing email templates')
            ->assertSee('Missing mailbox experience')
            ->assertSee('Missing compliance readiness');
    }

    public function test_bulk_activate_and_deactivate_readiness(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();

        $this->actingAs($admin)
            ->post(route('admin.locale-launch-center.bulk'), [
                'action' => 'activate',
                'locales' => ['he', 'ar'],
            ])
            ->assertRedirect(route('admin.locale-launch-center.index'));

        $this->assertDatabaseHas('locales', ['locale' => 'he', 'is_active' => true, 'launch_status' => 'ready']);
        $this->assertDatabaseHas('locales', ['locale' => 'ar', 'is_active' => true, 'launch_status' => 'ready']);

        $this->actingAs($admin)
            ->post(route('admin.locale-launch-center.bulk'), [
                'action' => 'deactivate',
                'locales' => ['he'],
            ])
            ->assertRedirect(route('admin.locale-launch-center.index'));

        $this->assertDatabaseHas('locales', ['locale' => 'he', 'is_active' => false, 'launch_status' => 'draft']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'locale.bulk_updated', 'actor_id' => $admin->id]);
    }

    public function test_publish_and_take_offline_actions_respect_default_safety(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $german = Locale::query()->where('locale', 'de')->firstOrFail();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.locale-launch-center.status', $german), ['status_action' => 'set_live'])
            ->assertRedirect(route('admin.locale-launch-center.index'));

        $this->assertDatabaseHas('locales', ['locale' => 'de', 'is_active' => true, 'launch_status' => 'launched']);

        $this->actingAs($admin)
            ->followingRedirects()
            ->patch(route('admin.locale-launch-center.status', $english), ['status_action' => 'take_offline'])
            ->assertOk()
            ->assertSee('The default locale cannot be taken offline');

        $this->assertDatabaseHas('locales', ['locale' => 'en', 'is_active' => true, 'is_default' => true]);
    }

    public function test_editor_can_view_but_cannot_manage_localization(): void
    {
        $editor = User::factory()->editor()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();

        $this->actingAs($editor)
            ->get(route('admin.locale-launch-center.index'))
            ->assertOk();

        $this->actingAs($editor)
            ->put(route('admin.locale-launch-center.update'), [
                'locales' => [
                    'en' => [
                        'market_readiness' => 'ready',
                        'is_active' => '1',
                        'is_default' => '1',
                        'sort_order' => 1,
                        'launch_status' => 'launched',
                    ],
                ],
            ])
            ->assertForbidden();
    }
}
