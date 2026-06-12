<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\Section;
use App\Models\SectionItem;
use App\Models\User;
use App\Services\Localization\LocaleSettingsStore;
use App\Services\Sections\SectionSearchService;
use App\Services\Sections\SectionTypeRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SectionsStudioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_sections_studio_renders_inside_admin_shell_and_replaces_placeholder(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');
        $section = Section::factory()->create([
            'locale_id' => $english->id,
            'title' => 'Private inbox CTA',
            'section_type' => 'cta',
            'placement' => 'home.primary',
            'created_by' => $admin->id,
        ]);
        SectionItem::factory()->create(['section_id' => $section->id, 'title' => 'FAQ readiness']);

        $this->actingAs($admin)
            ->get(route('admin.sections-studio.index'))
            ->assertOk()
            ->assertSee('Sections Studio')
            ->assertSee('Private inbox CTA')
            ->assertSee('Create foundation')
            ->assertDontSee('This workspace is ready for implementation.');
    }

    public function test_section_creation_requires_language_and_persists_foundation_fields(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');

        $this->actingAs($admin)
            ->from(route('admin.sections-studio.index'))
            ->post(route('admin.sections-studio.store'), [
                'section_type' => 'faq',
                'placement' => 'home.faq',
                'title' => 'FAQ block',
                'status' => 'draft',
                'visibility' => 'public',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('locale_id');

        $this->actingAs($admin)
            ->post(route('admin.sections-studio.store'), [
                'locale_id' => $english->id,
                'section_type' => 'faq',
                'placement' => 'home.faq',
                'title' => 'FAQ block',
                'subtitle' => 'Questions for disposable inbox users.',
                'content' => 'Foundation content.',
                'status' => 'active',
                'visibility' => 'public',
                'sort_order' => 3,
            ])
            ->assertRedirect(route('admin.sections-studio.index', ['section_type' => 'faq']));

        $this->assertDatabaseHas('sections', [
            'locale_id' => $english->id,
            'section_type' => 'faq',
            'placement' => 'home.faq',
            'title' => 'FAQ block',
            'status' => 'active',
            'sort_order' => 3,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'section.created', 'actor_id' => $admin->id]);
    }

    public function test_section_filters_search_language_type_placement_and_status(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');
        $german = $this->locale('de');

        Section::factory()->create([
            'locale_id' => $english->id,
            'title' => 'Trust center panel',
            'section_type' => 'trust_security',
            'placement' => 'home.secondary',
            'status' => 'active',
        ]);
        Section::factory()->create([
            'locale_id' => $german->id,
            'title' => 'German CTA',
            'section_type' => 'cta',
            'placement' => 'home.primary',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.sections-studio.index', [
                'q' => 'trust',
                'locale_id' => $english->id,
                'section_type' => 'trust_security',
                'placement' => 'home.secondary',
                'status' => 'active',
            ]));

        $response->assertOk()
            ->assertViewHas('sections', fn ($sections): bool => $sections->getCollection()->contains('title', 'Trust center panel')
                && ! $sections->getCollection()->contains('title', 'German CTA'))
            ->assertSee('Trust center panel')
            ->assertDontSee('German CTA');

        $results = app(SectionSearchService::class)->search(['status' => 'all']);
        $this->assertTrue($results->getCollection()->contains('title', 'Trust center panel'));
    }

    public function test_header_and_footer_are_not_editable_section_types(): void
    {
        $types = app(SectionTypeRegistry::class)->options();

        $this->assertArrayNotHasKey('header', $types);
        $this->assertArrayNotHasKey('footer', $types);
        $this->assertFalse(Schema::hasTable('section_translations'));
    }

    public function test_sections_studio_sources_do_not_use_forbidden_patterns(): void
    {
        $files = [
            app_path('Http/Controllers/Admin/SectionsStudioController.php'),
            app_path('Models/Section.php'),
            app_path('Models/SectionItem.php'),
            app_path('Services/Sections/SectionStore.php'),
            app_path('Services/Sections/SectionSearchService.php'),
            app_path('Services/Sections/SectionTypeRegistry.php'),
            app_path('Services/Sections/SectionPlacementRegistry.php'),
            app_path('Actions/Sections/CreateSectionAction.php'),
            app_path('Actions/Sections/UpdateSectionAction.php'),
            resource_path('views/dashboard/sections-studio/index.blade.php'),
            resource_path('views/components/sections/filter-bar.blade.php'),
            resource_path('views/components/sections/section-card.blade.php'),
            resource_path('views/components/sections/section-row.blade.php'),
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            $this->assertIsString($contents);
            $this->assertStringNotContainsString('Livewire', $contents, $file);
            $this->assertStringNotContainsString('livewire', $contents, $file);
            $this->assertStringNotContainsString('cdn.tailwindcss.com', $contents, $file);
            $this->assertStringNotContainsString('unpkg.com/alpine', $contents, $file);
            $this->assertStringNotContainsString('127.0.0.1', $contents, $file);
            $this->assertStringNotContainsString('section_translations', $contents, $file);
        }
    }

    private function locale(string $code): Locale
    {
        app(LocaleSettingsStore::class)->ensureSeeded();

        return Locale::query()->where('locale', $code)->firstOrFail();
    }
}
