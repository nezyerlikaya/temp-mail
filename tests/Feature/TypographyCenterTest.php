<?php

namespace Tests\Feature;

use App\Models\FontAssignment;
use App\Models\FontFamily;
use App\Models\Locale;
use App\Models\ThemeState;
use App\Models\User;
use App\Services\Localization\LocaleSettingsStore;
use App\Services\Typography\FontAssignmentService;
use App\Services\Typography\FontCoverageService;
use App\Services\Typography\FontStackResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TypographyCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_typography_center_renders_registry_and_assignment_panels(): void
    {
        app(LocaleSettingsStore::class)->ensureSeeded();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.typography-center.index'))
            ->assertOk()
            ->assertSee('Typography Center')
            ->assertSee('Font Registry')
            ->assertSee('Global Assignment')
            ->assertSee('Theme Assignment')
            ->assertSee('Locale Overrides')
            ->assertSee('Plus Jakarta Sans')
            ->assertSee('--tm-font-ui')
            ->assertDontSee('Module workspace coming next');

        $this->assertDatabaseHas('font_families', ['slug' => 'plus-jakarta-sans']);
        $this->assertDatabaseHas('font_assignments', ['scope' => 'global', 'scope_key' => 'default', 'usage' => 'ui']);
    }

    public function test_owner_admin_can_update_font_readiness_and_activation_state(): void
    {
        $admin = User::factory()->admin()->create();
        app(FontAssignmentService::class)->ensureDefaults();
        $family = FontFamily::query()->where('slug', 'inter')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.typography-center.families.update', $family), [
            'font_display' => 'optional',
            'local_file_ready' => '1',
            'media_ready' => '1',
        ])->assertRedirect();

        $family->refresh();
        $this->assertSame('optional', $family->font_display);
        $this->assertTrue($family->local_file_ready);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'typography.font_family_updated', 'actor_id' => $admin->id]);

        $this->actingAs($admin)->post(route('admin.typography-center.families.deactivate', $family))->assertRedirect();
        $this->assertFalse($family->refresh()->is_active);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'typography.font_family_deactivated', 'actor_id' => $admin->id]);
    }

    public function test_editor_cannot_view_or_manage_typography(): void
    {
        $editor = User::factory()->editor()->create();
        app(FontAssignmentService::class)->ensureDefaults();
        $family = FontFamily::query()->where('slug', 'inter')->firstOrFail();

        $this->actingAs($editor)->get(route('admin.typography-center.index'))->assertForbidden();
        $this->actingAs($editor)->put(route('admin.typography-center.families.update', $family), [
            'font_display' => 'swap',
        ])->assertForbidden();
        $this->actingAs($editor)->put(route('admin.typography-center.assignments.update'), [
            'scope' => 'global',
            'scope_key' => 'default',
            'assignments' => ['ui' => ['font_family_slug' => 'inter']],
        ])->assertForbidden();
    }

    public function test_assignments_are_allowlisted_audited_and_preserved_after_registry_seed(): void
    {
        $admin = User::factory()->admin()->create();
        app(FontAssignmentService::class)->ensureDefaults();

        $payload = [
            'scope' => 'global',
            'scope_key' => 'default',
            'assignments' => [
                'ui' => ['font_family_slug' => 'inter', 'fallback_stack' => ['system-sans', 'Arial']],
                'heading' => ['font_family_slug' => 'inter', 'fallback_stack' => ['system-sans']],
                'body' => ['font_family_slug' => 'noto-sans', 'fallback_stack' => ['system-sans']],
                'mono' => ['font_family_slug' => 'jetbrains-mono', 'fallback_stack' => ['system-mono']],
            ],
        ];

        $this->actingAs($admin)->put(route('admin.typography-center.assignments.update'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('font_assignments', [
            'scope' => 'global',
            'scope_key' => 'default',
            'usage' => 'ui',
            'font_family_slug' => 'inter',
            'updated_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'typography.assignments_updated', 'actor_id' => $admin->id]);

        app(FontAssignmentService::class)->ensureDefaults();
        $this->assertSame('inter', FontAssignment::query()->where('scope', 'global')->where('usage', 'ui')->value('font_family_slug'));

        $this->actingAs($admin)->from(route('admin.typography-center.index'))->put(route('admin.typography-center.assignments.update'), [
            'scope' => 'global',
            'scope_key' => 'default',
            'assignments' => [
                'ui' => ['font_family_slug' => 'inter', 'fallback_stack' => ['body{display:none}']],
            ],
        ])->assertRedirect(route('admin.typography-center.index'))
            ->assertSessionHasErrors(['assignments.ui.fallback_stack.0']);
    }

    public function test_font_stack_resolver_uses_locale_then_theme_then_global_priority_and_css_variables(): void
    {
        app(FontAssignmentService::class)->ensureDefaults();

        FontAssignment::query()->updateOrCreate(
            ['scope' => 'global', 'scope_key' => 'default', 'usage' => 'body'],
            ['font_family_slug' => 'inter', 'fallback_stack' => ['system-sans']],
        );
        FontAssignment::query()->updateOrCreate(
            ['scope' => 'theme', 'scope_key' => 'atlas', 'usage' => 'body'],
            ['font_family_slug' => 'noto-sans', 'fallback_stack' => ['system-sans']],
        );
        FontAssignment::query()->updateOrCreate(
            ['scope' => 'locale', 'scope_key' => 'ar', 'usage' => 'body'],
            ['font_family_slug' => 'noto-sans-arabic', 'fallback_stack' => ['system-sans']],
        );

        $resolver = app(FontStackResolver::class);

        $this->assertSame('theme', $resolver->resolve('atlas')['stacks']['body']['source']);
        $this->assertSame('locale', $resolver->resolve('atlas', 'ar')['stacks']['body']['source']);
        $this->assertStringContainsString("'Noto Sans Arabic'", $resolver->resolve('atlas', 'ar')['variables']['--tm-font-body']);
        $this->assertStringContainsString('--tm-font-ui:', $resolver->resolve('atlas', 'ar')['inline_style']);
    }

    public function test_public_theme_layout_receives_typography_css_variables_without_touching_admin_layout(): void
    {
        app(LocaleSettingsStore::class)->ensureSeeded();
        app(FontAssignmentService::class)->ensureDefaults();

        $html = view('themes.horizon.layout', ['title' => 'Public Temp Mail'])->render();

        $this->assertStringContainsString('--tm-font-ui:', $html);
        $this->assertStringContainsString('font-family: var(--tm-font-body);', $html);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('--tm-font-ui');
    }

    public function test_rtl_compatibility_warning_blocks_latin_only_locale_assignment(): void
    {
        app(LocaleSettingsStore::class)->ensureSeeded();
        app(FontAssignmentService::class)->ensureDefaults();
        $admin = User::factory()->admin()->create();
        $arabic = Locale::query()->where('locale', 'ar')->firstOrFail();
        $latin = FontFamily::query()->where('slug', 'plus-jakarta-sans')->firstOrFail();

        $warnings = app(FontCoverageService::class)->warningsForAssignment($latin, $arabic);
        $this->assertSame('warning', $warnings[0]['level']);

        $this->actingAs($admin)->from(route('admin.typography-center.index', ['locale' => 'ar']))->put(route('admin.typography-center.assignments.update'), [
            'scope' => 'locale',
            'scope_key' => 'ar',
            'assignments' => [
                'ui' => ['font_family_slug' => 'plus-jakarta-sans', 'fallback_stack' => ['system-sans']],
            ],
        ])->assertRedirect(route('admin.typography-center.index', ['locale' => 'ar']))
            ->assertSessionHasErrors(['assignments.ui.font_family_slug']);
    }

    public function test_typography_uses_real_route_no_livewire_no_alpine_or_google_cdn_and_admin_typography_unchanged(): void
    {
        $this->assertTrue(Route::has('admin.typography-center.index'));

        $paths = [
            app_path('Services/Typography'),
            app_path('Actions/Typography'),
            app_path('Http/Requests/Typography'),
            resource_path('views/components/typography'),
            resource_path('views/dashboard/typography-center'),
            base_path('routes/web.php'),
        ];

        $source = collect($paths)
            ->flatMap(fn (string $path) => File::isDirectory($path) ? File::allFiles($path) : [$path])
            ->map(fn ($file): string => File::get((string) $file))
            ->implode("\n");

        $this->assertStringNotContainsString('fonts.googleapis', $source);
        $this->assertStringNotContainsString('fonts.gstatic', $source);
        $this->assertStringNotContainsString('cdn.tailwindcss', $source);
        $this->assertStringNotContainsString('Livewire', $source);
        $this->assertStringNotContainsString('livewire', $source);
        $this->assertStringNotContainsString('127.0.0.1', $source);

        ThemeState::query()->create(['slug' => 'horizon', 'status' => 'active', 'last_activated_at' => now()]);
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('--tm-font-ui');
    }
}
