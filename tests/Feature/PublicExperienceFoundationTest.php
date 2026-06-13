<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\ThemeState;
use App\Models\TranslationSource;
use App\Models\TranslationValue;
use App\Models\User;
use App\Services\Installer\InstallState;
use App\Services\Themes\ThemeCacheService;
use App\Services\Translations\TranslationStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PublicExperienceFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(InstallState::class)->lock();
        app(TranslationStore::class)->syncRegistry();
        $this->seedLocales();
    }

    protected function tearDown(): void
    {
        File::delete(app(InstallState::class)->lockPath());
        File::delete(app(InstallState::class)->legacyLockPath());

        parent::tearDown();
    }

    public function test_root_redirects_to_default_public_locale_after_install(): void
    {
        $this->get(route('home'))
            ->assertRedirect(route('public.home', ['locale' => 'en']));
    }

    public function test_public_home_renders_horizon_with_prepared_theme_data_and_vite(): void
    {
        $this->activateTheme('horizon');

        $this->get(route('public.home', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('data-public-theme="horizon"', false)
            ->assertSee('Private temporary email in seconds')
            ->assertSee('Skip to content');

        $this->assertStringContainsString('@vite', File::get(resource_path('views/themes/horizon/layouts/public.blade.php')));
    }

    public function test_all_registered_public_theme_layouts_render_from_active_theme(): void
    {
        foreach (['horizon', 'atlas', 'legacy'] as $theme) {
            $this->activateTheme($theme);

            $this->get(route('public.home', ['locale' => 'en']))
                ->assertOk()
                ->assertSee('data-public-theme="'.$theme.'"', false)
                ->assertSee('Private temporary email in seconds');
        }
    }

    public function test_rtl_locale_sets_document_direction_and_locale_switcher_uses_named_routes(): void
    {
        $this->get(route('public.home', ['locale' => 'ar']))
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee(route('public.home', ['locale' => 'en']), false)
            ->assertDontSee('127.0.0.1:8000', false);
    }

    public function test_inactive_or_offline_locale_is_not_publicly_accessible(): void
    {
        $this->get('/de')->assertNotFound();
        $this->get('/fr')->assertNotFound();
    }

    public function test_missing_or_unpublished_translation_uses_english_source_fallback(): void
    {
        $locale = Locale::query()->where('locale', 'ar')->firstOrFail();
        $source = TranslationSource::query()->where('translation_key', 'home.hero.title')->firstOrFail();

        TranslationValue::query()->create([
            'translation_source_id' => $source->id,
            'locale_id' => $locale->id,
            'value' => 'Draft Arabic title',
            'status' => 'draft',
            'updated_by' => User::factory()->admin()->create()->id,
        ]);

        $this->get(route('public.home', ['locale' => 'ar']))
            ->assertOk()
            ->assertSee('Private temporary email in seconds')
            ->assertDontSee('Draft Arabic title');
    }

    public function test_public_route_structure_is_named_and_admin_layout_remains_available(): void
    {
        $this->assertTrue(Route::has('public.home'));

        User::factory()->admin()->create();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Forgot password');
    }

    public function test_public_theme_blades_do_not_contain_database_queries_or_service_resolution(): void
    {
        foreach (File::allFiles(resource_path('views/themes')) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $contents = File::get($file->getPathname());

            $this->assertStringNotContainsString('DB::', $contents, $file->getPathname());
            $this->assertStringNotContainsString('::query(', $contents, $file->getPathname());
            $this->assertStringNotContainsString('app(', $contents, $file->getPathname());
            $this->assertStringNotContainsString('App\\Services\\', $contents, $file->getPathname());
        }
    }

    private function activateTheme(string $slug): void
    {
        ThemeState::query()->delete();

        foreach (['horizon', 'atlas', 'legacy'] as $theme) {
            ThemeState::query()->create([
                'slug' => $theme,
                'status' => $theme === $slug ? 'active' : 'inactive',
                'last_activated_at' => $theme === $slug ? now() : null,
            ]);
        }

        app(ThemeCacheService::class)->clear();
    }

    private function seedLocales(): void
    {
        Locale::query()->create([
            'language_name' => 'English',
            'native_name' => 'English',
            'locale' => 'en',
            'direction' => 'ltr',
            'region' => 'Global',
            'market_readiness' => 'ready',
            'is_active' => true,
            'is_default' => true,
            'sort_order' => 1,
            'launch_status' => 'launched',
        ]);

        Locale::query()->create([
            'language_name' => 'Arabic',
            'native_name' => 'العربية',
            'locale' => 'ar',
            'direction' => 'rtl',
            'region' => 'MENA',
            'market_readiness' => 'ready',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 2,
            'launch_status' => 'launched',
        ]);

        Locale::query()->create([
            'language_name' => 'German',
            'native_name' => 'Deutsch',
            'locale' => 'de',
            'direction' => 'ltr',
            'region' => 'DACH',
            'market_readiness' => 'ready',
            'is_active' => false,
            'is_default' => false,
            'sort_order' => 3,
            'launch_status' => 'launched',
        ]);

        Locale::query()->create([
            'language_name' => 'French',
            'native_name' => 'Français',
            'locale' => 'fr',
            'direction' => 'ltr',
            'region' => 'France',
            'market_readiness' => 'ready',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 4,
            'launch_status' => 'paused',
        ]);
    }
}
