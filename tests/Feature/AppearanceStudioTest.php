<?php

namespace Tests\Feature;

use App\Models\AppearanceSetting;
use App\Models\ThemeState;
use App\Models\User;
use App\Services\Appearance\AppearanceTokenRegistry;
use App\Services\Appearance\AppearanceTokenResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AppearanceStudioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_appearance_studio_renders_defaults_for_each_fixed_theme(): void
    {
        $admin = User::factory()->admin()->create();
        $registry = app(AppearanceTokenRegistry::class);

        $this->actingAs($admin)->get(route('admin.appearance-studio.index'))
            ->assertOk()
            ->assertSee('Appearance Studio')
            ->assertSee('Horizon')
            ->assertSee('Atlas')
            ->assertSee('Legacy')
            ->assertSee('Brand color')
            ->assertSee('Use theme defaults')
            ->assertSee('--tm-brand-color')
            ->assertDontSee('Module workspace coming next');

        $this->assertSame(['horizon', 'atlas', 'legacy'], array_keys($registry->defaults()));
        foreach (['horizon', 'atlas', 'legacy'] as $theme) {
            $this->assertCount(11, $registry->defaultFor($theme));
        }
    }

    public function test_owner_admin_can_save_safe_draft_tokens_per_theme(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.appearance-studio.update'), [
            'theme' => 'atlas',
            'mode' => 'custom',
            'tokens' => $this->tokens(['brand_color' => '#123456', 'shadow_level' => 'medium']),
        ])->assertRedirect(route('admin.appearance-studio.index', ['theme' => 'atlas']));

        $this->assertDatabaseHas('appearance_settings', [
            'theme_slug' => 'atlas',
            'mode' => 'custom',
            'updated_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'appearance.draft_updated', 'actor_id' => $admin->id]);

        $this->assertSame('#123456', AppearanceSetting::query()->where('theme_slug', 'atlas')->first()->draft_tokens['brand_color']);
        $this->assertDatabaseMissing('appearance_settings', ['theme_slug' => 'legacy', 'mode' => 'custom']);
    }

    public function test_unsafe_token_names_color_css_and_options_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $payload = [
            'theme' => 'horizon',
            'mode' => 'custom',
            'tokens' => $this->tokens([
                'brand_color' => 'url(javascript:alert(1))',
                'shadow_level' => 'shadow-xl',
                'custom_css' => 'body{display:none}',
            ]),
        ];

        $this->actingAs($admin)->from(route('admin.appearance-studio.index'))->put(route('admin.appearance-studio.update'), $payload)
            ->assertRedirect(route('admin.appearance-studio.index'))
            ->assertSessionHasErrors(['tokens.brand_color', 'tokens.shadow_level', 'tokens']);

        $this->assertDatabaseCount('appearance_settings', 0);
    }

    public function test_reset_individual_token_and_all_tokens_restore_theme_defaults(): void
    {
        $admin = User::factory()->admin()->create();
        $defaults = app(AppearanceTokenRegistry::class)->defaultFor('horizon');

        $this->actingAs($admin)->put(route('admin.appearance-studio.update'), [
            'theme' => 'horizon',
            'mode' => 'custom',
            'tokens' => $this->tokens(['brand_color' => '#123456', 'accent_color' => '#654321']),
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('admin.appearance-studio.reset-token'), [
            'theme' => 'horizon',
            'token' => 'brand_color',
        ])->assertRedirect(route('admin.appearance-studio.index', ['theme' => 'horizon']));

        $setting = AppearanceSetting::query()->where('theme_slug', 'horizon')->first();
        $this->assertSame($defaults['brand_color'], $setting->draft_tokens['brand_color']);
        $this->assertSame('#654321', $setting->draft_tokens['accent_color']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'appearance.token_reset', 'actor_id' => $admin->id]);

        $this->actingAs($admin)->post(route('admin.appearance-studio.reset'), [
            'theme' => 'horizon',
            'confirmation' => '1',
        ])->assertRedirect(route('admin.appearance-studio.index', ['theme' => 'horizon']));

        $setting->refresh();
        $this->assertSame('defaults', $setting->mode);
        $this->assertSame($defaults, $setting->draft_tokens);
        $this->assertNull($setting->published_tokens);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'appearance.reset', 'actor_id' => $admin->id]);
    }

    public function test_editor_cannot_view_update_or_reset_appearance(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get(route('admin.appearance-studio.index'))->assertForbidden();

        $this->actingAs($editor)->put(route('admin.appearance-studio.update'), [
            'theme' => 'horizon',
            'mode' => 'custom',
            'tokens' => $this->tokens(),
        ])->assertForbidden();

        $this->actingAs($editor)->post(route('admin.appearance-studio.reset'), [
            'theme' => 'horizon',
            'confirmation' => '1',
        ])->assertForbidden();
    }

    public function test_active_theme_resolver_loads_saved_or_default_public_css_variables_only(): void
    {
        ThemeState::query()->create(['slug' => 'atlas', 'status' => 'active', 'last_activated_at' => now()]);
        ThemeState::query()->create(['slug' => 'horizon', 'status' => 'inactive']);
        $tokens = app(AppearanceTokenResolver::class)->activePublicTokens();

        $this->assertSame('atlas', $tokens['theme']);
        $this->assertArrayHasKey('--tm-brand-color', $tokens['variables']);
        $this->assertStringContainsString('--tm-brand-color:', $tokens['inline_style']);
        $this->assertStringNotContainsString('custom_css', $tokens['inline_style']);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('--tm-brand-color');
    }

    public function test_routes_are_named_and_no_upload_custom_css_or_delete_route_exists(): void
    {
        $this->assertTrue(Route::has('admin.appearance-studio.index'));
        $this->assertTrue(Route::has('admin.appearance-studio.update'));
        $this->assertTrue(Route::has('admin.appearance-studio.reset'));
        $this->assertTrue(Route::has('admin.appearance-studio.reset-token'));
        $this->assertFalse(Route::has('admin.appearance-studio.upload'));
        $this->assertFalse(Route::has('admin.appearance-studio.delete'));
        $this->assertFalse(Route::has('admin.appearance-studio.custom-css'));
    }

    /** @param array<string, string> $overrides */
    private function tokens(array $overrides = []): array
    {
        return [
            ...app(AppearanceTokenRegistry::class)->defaultFor('horizon'),
            ...$overrides,
        ];
    }
}
