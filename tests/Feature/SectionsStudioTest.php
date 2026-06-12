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
            ->assertSee('Create section')
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
            ->assertRedirect();

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

    public function test_section_create_and_edit_editor_render_with_type_specific_controls(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');
        $section = Section::factory()->create([
            'locale_id' => $english->id,
            'created_by' => $admin->id,
            'section_type' => 'cta',
            'title' => 'Start using private inboxes',
            'settings' => ['button_label' => 'Create inbox', 'button_url' => '/en'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.sections-studio.create'))
            ->assertOk()
            ->assertSee('Create section')
            ->assertSee('Section type')
            ->assertSee('Device visibility')
            ->assertSee('Button label')
            ->assertSee('Post count');

        $this->actingAs($admin)
            ->get(route('admin.sections-studio.edit', $section))
            ->assertOk()
            ->assertSee('Start using private inboxes')
            ->assertSee('Create inbox')
            ->assertSee('You have unsaved changes.');
    }

    public function test_cta_and_blog_teaser_settings_and_device_visibility_are_persisted(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');

        $this->actingAs($admin)
            ->post(route('admin.sections-studio.store'), [
                'locale_id' => $english->id,
                'section_type' => 'cta',
                'placement' => 'home.primary',
                'title' => 'Mailbox CTA',
                'subtitle' => 'Create an inbox in one click.',
                'content' => 'CTA content.',
                'settings' => [
                    'button_label' => 'Create inbox',
                    'button_url' => '/en',
                ],
                'status' => 'active',
                'visibility' => 'public',
                'device_visibility' => 'mobile',
                'sort_order' => 1,
            ])
            ->assertRedirect();

        $section = Section::query()->where('title', 'Mailbox CTA')->firstOrFail();
        $this->assertSame('mobile', $section->device_visibility);
        $this->assertSame('Create inbox', $section->settings['button_label']);

        $this->actingAs($admin)
            ->put(route('admin.sections-studio.update', $section), [
                'locale_id' => $english->id,
                'section_type' => 'blog_teaser',
                'placement' => 'home.secondary',
                'title' => 'Latest privacy guides',
                'subtitle' => 'Blog teaser.',
                'content' => '',
                'settings' => [
                    'post_count' => 6,
                    'layout' => 'grid',
                ],
                'status' => 'hidden',
                'visibility' => 'public',
                'device_visibility' => 'desktop',
                'sort_order' => 2,
            ])
            ->assertRedirect(route('admin.sections-studio.edit', $section));

        $section->refresh();
        $this->assertSame('blog_teaser', $section->section_type);
        $this->assertSame('desktop', $section->device_visibility);
        $this->assertSame(6, $section->settings['post_count']);
    }

    public function test_faq_items_can_be_added_updated_reordered_and_soft_removed(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');
        $section = Section::factory()->create([
            'locale_id' => $english->id,
            'created_by' => $admin->id,
            'section_type' => 'faq',
            'placement' => 'home.faq',
        ]);

        foreach (['What is temp mail?', 'How long does inbox last?', 'Can I receive attachments?', 'Is it private?'] as $question) {
            $this->actingAs($admin)
                ->post(route('admin.sections-studio.items.store', $section), [
                    'title' => $question,
                    'content' => 'Answer for '.$question,
                    'status' => 'active',
                ])
                ->assertRedirect(route('admin.sections-studio.edit', $section));
        }

        $items = $section->items()->orderBy('sort_order')->get();
        $this->assertCount(4, $items);

        $first = $items->firstOrFail();
        $this->actingAs($admin)
            ->put(route('admin.sections-studio.items.update', [$section, $first]), [
                'title' => 'What is disposable email?',
                'content' => 'Updated answer.',
                'status' => 'inactive',
                'sort_order' => $first->sort_order,
            ])
            ->assertRedirect(route('admin.sections-studio.edit', $section));

        $order = $items->pluck('id')->reverse()->values()->all();
        $this->actingAs($admin)
            ->post(route('admin.sections-studio.items.reorder', $section), ['order' => $order])
            ->assertRedirect(route('admin.sections-studio.edit', $section));

        $this->assertSame($order, $section->items()->orderBy('sort_order')->pluck('id')->all());

        $last = $section->items()->orderByDesc('sort_order')->firstOrFail();
        $this->actingAs($admin)
            ->delete(route('admin.sections-studio.items.destroy', [$section, $last]), ['confirm_remove' => '1'])
            ->assertRedirect(route('admin.sections-studio.edit', $section));

        $this->assertDatabaseHas('section_items', ['id' => $last->id, 'status' => 'removed']);

        $this->actingAs($admin)
            ->get(route('admin.sections-studio.edit', $section))
            ->assertOk()
            ->assertSee('FAQ quality')
            ->assertSee('Minimum 4, ideal 6-8, maximum 12.');
    }

    public function test_duplicate_faq_question_is_rejected_with_field_error(): void
    {
        $admin = User::factory()->admin()->create();
        $section = Section::factory()->create([
            'locale_id' => $this->locale('en')->id,
            'section_type' => 'faq',
        ]);
        SectionItem::factory()->create([
            'section_id' => $section->id,
            'title' => 'Is temp mail private?',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.sections-studio.edit', $section))
            ->post(route('admin.sections-studio.items.store', $section), [
                'title' => 'Is temp mail private?',
                'content' => 'Duplicate answer.',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.sections-studio.edit', $section))
            ->assertSessionHasErrors('title');
    }

    public function test_section_ordering_is_scoped_to_same_language_and_placement(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');
        $german = $this->locale('de');
        $first = Section::factory()->create(['locale_id' => $english->id, 'placement' => 'home.primary', 'sort_order' => 0]);
        $second = Section::factory()->create(['locale_id' => $english->id, 'placement' => 'home.primary', 'sort_order' => 1]);
        $foreign = Section::factory()->create(['locale_id' => $german->id, 'placement' => 'home.primary', 'sort_order' => 0]);

        $this->actingAs($admin)
            ->post(route('admin.sections-studio.reorder'), [
                'locale_id' => $english->id,
                'placement' => 'home.primary',
                'order' => [$second->id, $first->id],
            ])
            ->assertRedirect();

        $this->assertSame([$second->id, $first->id], Section::query()
            ->where('locale_id', $english->id)
            ->where('placement', 'home.primary')
            ->orderBy('sort_order')
            ->pluck('id')
            ->all());

        $this->actingAs($admin)
            ->from(route('admin.sections-studio.index'))
            ->post(route('admin.sections-studio.reorder'), [
                'locale_id' => $english->id,
                'placement' => 'home.primary',
                'order' => [$first->id, $foreign->id],
            ])
            ->assertRedirect(route('admin.sections-studio.index'))
            ->assertSessionHasErrors('order');
    }

    public function test_active_passive_and_device_visibility_values_are_validated(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');

        $this->actingAs($admin)
            ->from(route('admin.sections-studio.create'))
            ->post(route('admin.sections-studio.store'), [
                'locale_id' => $english->id,
                'section_type' => 'cta',
                'placement' => 'home.primary',
                'title' => 'Invalid state',
                'status' => 'disabled',
                'visibility' => 'public',
                'device_visibility' => 'tablet',
            ])
            ->assertRedirect(route('admin.sections-studio.create'))
            ->assertSessionHasErrors(['status', 'device_visibility']);
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
            resource_path('views/components/sections/section-editor.blade.php'),
            resource_path('views/components/sections/faq-item-editor.blade.php'),
            resource_path('views/components/sections/item-list.blade.php'),
            resource_path('views/dashboard/sections-studio/create.blade.php'),
            resource_path('views/dashboard/sections-studio/edit.blade.php'),
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
