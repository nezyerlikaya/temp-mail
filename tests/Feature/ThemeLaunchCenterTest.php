<?php

namespace Tests\Feature;

use App\Models\ThemeState;
use App\Models\User;
use App\Services\Themes\ThemeManager;
use App\Services\Themes\ThemeRegistry;
use App\Services\Themes\ThemeResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ThemeLaunchCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_theme_launch_center_renders_fixed_registry_with_horizon_default(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.theme-launch-center.index'))
            ->assertOk()
            ->assertSee('Theme Launch Center')
            ->assertSee('Horizon')
            ->assertSee('Atlas')
            ->assertSee('Legacy')
            ->assertSee('3 registered')
            ->assertSee('Active public theme')
            ->assertDontSee('Module workspace coming next');

        $this->assertDatabaseHas('theme_states', ['slug' => 'horizon', 'status' => 'active']);
        $this->assertSame(1, ThemeState::query()->where('status', 'active')->count());
    }

    public function test_activation_deactivates_previous_theme_audits_and_supports_rollback_readiness(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.theme-launch-center.activate'), [
            'theme' => 'atlas',
            'confirmation' => '1',
        ])->assertRedirect(route('admin.theme-launch-center.index'));

        $this->assertDatabaseHas('theme_states', ['slug' => 'horizon', 'status' => 'inactive']);
        $this->assertDatabaseHas('theme_states', ['slug' => 'atlas', 'status' => 'active', 'activated_by' => $admin->id]);
        $this->assertSame(1, ThemeState::query()->where('status', 'active')->count());
        $this->assertDatabaseHas('user_audit_events', ['event' => 'theme.activated', 'actor_id' => $admin->id]);

        $readiness = app(ThemeManager::class)->rollbackReadiness();
        $this->assertTrue($readiness['ready']);
        $this->assertSame('horizon', $readiness['slug']);
    }

    public function test_active_theme_cannot_be_activated_again_and_confirmation_is_required(): void
    {
        $admin = User::factory()->admin()->create();
        app(ThemeManager::class)->ensureRegisteredThemes();

        $this->actingAs($admin)->post(route('admin.theme-launch-center.activate'), [
            'theme' => 'atlas',
        ])->assertSessionHasErrors(['confirmation']);

        $this->actingAs($admin)->post(route('admin.theme-launch-center.activate'), [
            'theme' => 'horizon',
            'confirmation' => '1',
        ])->assertSessionHasErrors(['theme']);

        $this->assertSame('horizon', app(ThemeResolver::class)->active()['slug']);
        $this->assertSame(1, ThemeState::query()->where('status', 'active')->count());
    }

    public function test_only_owner_and_admin_can_activate_theme(): void
    {
        $editor = User::factory()->editor()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($editor)->post(route('admin.theme-launch-center.activate'), [
            'theme' => 'atlas',
            'confirmation' => '1',
        ])->assertForbidden();

        $this->actingAs($admin)->post(route('admin.theme-launch-center.activate'), [
            'theme' => 'atlas',
            'confirmation' => '1',
        ])->assertRedirect(route('admin.theme-launch-center.index'));
    }

    public function test_registry_has_exactly_three_themes_and_public_view_structure_exists(): void
    {
        $registry = app(ThemeRegistry::class);

        $this->assertSame(['horizon', 'atlas', 'legacy'], $registry->slugs());

        foreach ($registry->slugs() as $slug) {
            $base = resource_path('views/themes/'.$slug);
            $this->assertDirectoryExists($base);
            $this->assertFileExists($base.'/layout.blade.php');
            $this->assertFileExists($base.'/partials/header.blade.php');
            $this->assertFileExists($base.'/partials/footer.blade.php');
            $this->assertFileExists($base.'/home.blade.php');
            $this->assertFileExists($base.'/mailbox.blade.php');
            $this->assertFileExists($base.'/blog/index.blade.php');
            $this->assertFileExists($base.'/pages/show.blade.php');
            $this->assertFileExists($base.'/sections/index.blade.php');
        }
    }

    public function test_routes_are_named_and_no_theme_delete_route_exists(): void
    {
        $this->assertTrue(Route::has('admin.theme-launch-center.index'));
        $this->assertTrue(Route::has('admin.theme-launch-center.activate'));
        $this->assertFalse(Route::has('admin.theme-launch-center.delete'));
        $this->assertFalse(Route::has('admin.theme-launch-center.destroy'));
    }

    public function test_activation_lock_prevents_concurrent_theme_changes(): void
    {
        $admin = User::factory()->admin()->create();
        $lockPath = storage_path('app/theme-launch/theme-activation.lock');
        File::ensureDirectoryExists(dirname($lockPath));
        File::put($lockPath, json_encode(['owner' => 'test']));

        try {
            $this->actingAs($admin)->post(route('admin.theme-launch-center.activate'), [
                'theme' => 'atlas',
                'confirmation' => '1',
            ])->assertSessionHasErrors(['theme']);

            $this->assertDatabaseMissing('theme_states', ['slug' => 'atlas', 'status' => 'active']);
        } finally {
            File::delete($lockPath);
        }
    }

    public function test_activation_service_does_not_expose_upload_custom_css_or_deletion_actions(): void
    {
        $this->assertFileDoesNotExist(app_path('Http/Requests/Themes/UploadThemeRequest.php'));

        $source = File::get(resource_path('views/dashboard/theme-launch-center/index.blade.php'))
            .File::get(resource_path('views/components/themes/theme-card.blade.php'));

        $this->assertStringNotContainsString('custom_css', $source);
        $this->assertStringNotContainsString('type="file"', $source);
        $this->assertStringNotContainsString('delete', strtolower($source));
    }
}
