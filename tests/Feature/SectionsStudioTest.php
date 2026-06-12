<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Locale;
use App\Models\Section;
use App\Models\SectionItem;
use App\Models\User;
use App\Services\Localization\LocaleSettingsStore;
use App\Services\Sections\SectionCollectionResolver;
use App\Services\Sections\SectionRenderService;
use App\Services\Sections\SectionSearchService;
use App\Services\Sections\SectionSeoReadinessService;
use App\Services\Sections\SectionTypeRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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

    public function test_section_lifecycle_trash_restore_and_permanent_delete_are_audited(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');
        $section = Section::factory()->create([
            'locale_id' => $english->id,
            'status' => 'draft',
            'title' => 'Lifecycle CTA',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.sections-studio.activate', $section))
            ->assertRedirect(route('admin.sections-studio.edit', $section));

        $this->assertDatabaseHas('sections', ['id' => $section->id, 'status' => 'active']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'section.activated', 'actor_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.sections-studio.hide', $section))
            ->assertRedirect(route('admin.sections-studio.edit', $section));

        $this->assertDatabaseHas('sections', ['id' => $section->id, 'status' => 'hidden']);

        $this->actingAs($admin)
            ->post(route('admin.sections-studio.trash', $section), ['confirm_trash' => '1'])
            ->assertRedirect(route('admin.sections-studio.index', ['status' => 'trashed']));

        $this->assertDatabaseHas('sections', ['id' => $section->id, 'status' => 'trashed']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'section.trashed', 'actor_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.sections-studio.restore', $section))
            ->assertRedirect(route('admin.sections-studio.edit', $section));

        $this->assertDatabaseHas('sections', ['id' => $section->id, 'status' => 'draft']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'section.restored', 'actor_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.sections-studio.trash', $section), ['confirm_trash' => '1'])
            ->assertRedirect(route('admin.sections-studio.index', ['status' => 'trashed']));

        $this->actingAs($admin)
            ->delete(route('admin.sections-studio.destroy', $section), ['confirm_delete' => '1'])
            ->assertRedirect(route('admin.sections-studio.index', ['status' => 'trashed']));

        $this->assertDatabaseMissing('sections', ['id' => $section->id]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'section.permanently_deleted', 'actor_id' => $admin->id]);
    }

    public function test_editor_can_trash_but_cannot_permanently_delete_sections(): void
    {
        $editor = User::factory()->editor()->create();
        $section = Section::factory()->create([
            'locale_id' => $this->locale('en')->id,
            'status' => 'trashed',
        ]);

        $this->actingAs($editor)
            ->delete(route('admin.sections-studio.destroy', $section), ['confirm_delete' => '1'])
            ->assertForbidden();

        $this->assertDatabaseHas('sections', ['id' => $section->id]);
    }

    public function test_trash_filter_shows_trashed_sections_only_when_requested(): void
    {
        $admin = User::factory()->admin()->create();
        $active = Section::factory()->create([
            'locale_id' => $this->locale('en')->id,
            'title' => 'Visible CTA',
            'status' => 'active',
        ]);
        $trashed = Section::factory()->create([
            'locale_id' => $this->locale('en')->id,
            'title' => 'Archived FAQ',
            'status' => 'trashed',
            'trashed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.sections-studio.index'))
            ->assertOk()
            ->assertSee($active->title)
            ->assertDontSee($trashed->title);

        $response = $this->actingAs($admin)
            ->get(route('admin.sections-studio.index', ['status' => 'trashed']));

        $response->assertOk()
            ->assertViewHas('sections', fn ($sections): bool => $sections->getCollection()->contains('title', 'Archived FAQ')
                && ! $sections->getCollection()->contains('title', 'Visible CTA'))
            ->assertSee('Archived FAQ');
    }

    public function test_render_resolver_is_language_placement_device_scoped_and_ignores_hidden_or_trashed_records(): void
    {
        $english = $this->locale('en');
        $german = $this->locale('de');
        $active = Section::factory()->create([
            'locale_id' => $english->id,
            'placement' => 'home.primary',
            'section_type' => 'cta',
            'title' => 'English CTA',
            'status' => 'active',
            'device_visibility' => 'all',
            'settings' => ['button_label' => 'Create inbox'],
        ]);
        Section::factory()->create(['locale_id' => $english->id, 'placement' => 'home.primary', 'status' => 'hidden']);
        Section::factory()->create(['locale_id' => $english->id, 'placement' => 'home.primary', 'status' => 'trashed']);
        Section::factory()->create(['locale_id' => $german->id, 'placement' => 'home.primary', 'status' => 'active']);
        Section::factory()->create(['locale_id' => $english->id, 'placement' => 'home.secondary', 'status' => 'active']);

        $resolved = app(SectionCollectionResolver::class)->resolve($english, 'home.primary');
        $renderable = app(SectionRenderService::class)->renderableCollection($resolved);

        $this->assertSame([$active->id], $resolved->pluck('id')->all());
        $this->assertSame('English CTA', $renderable->first()['title']);
    }

    public function test_missing_or_unready_faq_cta_and_blog_teaser_render_no_placeholder(): void
    {
        $english = $this->locale('en');
        $render = app(SectionRenderService::class);
        $faq = Section::factory()->create([
            'locale_id' => $english->id,
            'section_type' => 'faq',
            'status' => 'active',
        ]);
        $cta = Section::factory()->create([
            'locale_id' => $english->id,
            'section_type' => 'cta',
            'title' => '',
            'status' => 'active',
            'settings' => [],
        ]);
        $teaser = Section::factory()->create([
            'locale_id' => $english->id,
            'section_type' => 'blog_teaser',
            'status' => 'active',
        ]);

        $this->assertNull($render->renderable($faq));
        $this->assertNull($render->renderable($cta));
        $this->assertNull($render->renderable($teaser));

        BlogPost::factory()->create([
            'locale_id' => $english->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->assertSame('blog_teaser', $render->renderable($teaser)['type']);
    }

    public function test_faq_schema_readiness_follows_count_rules(): void
    {
        $section = Section::factory()->create([
            'locale_id' => $this->locale('en')->id,
            'section_type' => 'faq',
            'status' => 'active',
        ]);

        foreach (range(1, 3) as $index) {
            SectionItem::factory()->create(['section_id' => $section->id, 'title' => 'Question '.$index, 'status' => 'active']);
        }

        $readiness = app(SectionSeoReadinessService::class)->forSection($section->fresh('items'));
        $this->assertFalse($readiness['schema_allowed']);
        $this->assertSame(3, $readiness['active_count']);

        SectionItem::factory()->create(['section_id' => $section->id, 'title' => 'Question 4', 'status' => 'active']);
        $readiness = app(SectionSeoReadinessService::class)->forSection($section->fresh('items'));
        $this->assertTrue($readiness['schema_allowed']);
        $this->assertFalse($readiness['ideal']);

        foreach (range(5, 6) as $index) {
            SectionItem::factory()->create(['section_id' => $section->id, 'title' => 'Question '.$index, 'status' => 'active']);
        }

        $readiness = app(SectionSeoReadinessService::class)->forSection($section->fresh('items'));
        $this->assertTrue($readiness['schema_allowed']);
        $this->assertTrue($readiness['ideal']);

        foreach (range(7, 13) as $index) {
            SectionItem::factory()->create(['section_id' => $section->id, 'title' => 'Question '.$index, 'status' => 'active']);
        }

        $readiness = app(SectionSeoReadinessService::class)->forSection($section->fresh('items'));
        $this->assertTrue($readiness['schema_allowed']);
        $this->assertContains('Maximum recommended FAQ schema coverage is 12 active questions.', $readiness['warnings']);
    }

    public function test_signed_section_preview_requires_signature_and_renders_readiness_panels(): void
    {
        $admin = User::factory()->admin()->create();
        $section = Section::factory()->create([
            'locale_id' => $this->locale('en')->id,
            'section_type' => 'cta',
            'status' => 'active',
            'settings' => ['button_label' => 'Create inbox'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.sections-studio.preview', $section))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(URL::temporarySignedRoute('admin.sections-studio.preview', now()->addMinutes(5), $section))
            ->assertOk()
            ->assertSee('Signed preview')
            ->assertSee('Render readiness')
            ->assertSee('Theme contract');
    }

    public function test_sections_studio_sources_do_not_use_forbidden_patterns(): void
    {
        $files = [
            app_path('Http/Controllers/Admin/SectionsStudioController.php'),
            app_path('Models/Section.php'),
            app_path('Models/SectionItem.php'),
            app_path('Services/Sections/SectionLifecycleService.php'),
            app_path('Services/Sections/SectionRenderService.php'),
            app_path('Services/Sections/SectionCollectionResolver.php'),
            app_path('Services/Sections/SectionSeoReadinessService.php'),
            app_path('Services/Sections/SectionThemeContractService.php'),
            app_path('Services/Sections/SectionAuditLogger.php'),
            app_path('Services/Sections/SectionStore.php'),
            app_path('Services/Sections/SectionSearchService.php'),
            app_path('Services/Sections/SectionTypeRegistry.php'),
            app_path('Services/Sections/SectionPlacementRegistry.php'),
            app_path('Actions/Sections/ActivateSectionAction.php'),
            app_path('Actions/Sections/CreateSectionAction.php'),
            app_path('Actions/Sections/DeleteSectionAction.php'),
            app_path('Actions/Sections/HideSectionAction.php'),
            app_path('Actions/Sections/RestoreSectionAction.php'),
            app_path('Actions/Sections/TrashSectionAction.php'),
            app_path('Actions/Sections/UpdateSectionAction.php'),
            resource_path('views/dashboard/sections-studio/index.blade.php'),
            resource_path('views/dashboard/sections-studio/preview.blade.php'),
            resource_path('views/components/sections/filter-bar.blade.php'),
            resource_path('views/components/sections/section-card.blade.php'),
            resource_path('views/components/sections/section-row.blade.php'),
            resource_path('views/components/sections/lifecycle-actions.blade.php'),
            resource_path('views/components/sections/preview-button.blade.php'),
            resource_path('views/components/sections/delete-warning.blade.php'),
            resource_path('views/components/sections/render-readiness.blade.php'),
            resource_path('views/components/sections/seo-readiness.blade.php'),
            resource_path('views/components/sections/theme-contract.blade.php'),
            resource_path('views/components/sections/trash-filter.blade.php'),
            resource_path('views/components/sections/status-badge.blade.php'),
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
