<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\User;
use App\Services\Localization\LocaleSettingsStore;
use App\Services\Pages\PagePreviewService;
use App\Services\Pages\PageSearchService;
use App\Services\Settings\SettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class PageStudioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_page_studio_renders_inside_admin_shell_and_replaces_placeholder(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $page = Page::factory()->create([
            'locale_id' => Locale::query()->where('locale', 'en')->firstOrFail()->id,
            'author_id' => $admin->id,
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'page_type' => 'privacy_policy',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.page-studio.index'))
            ->assertOk()
            ->assertSee('Page Studio')
            ->assertSee('Page records')
            ->assertSee('Publishing readiness')
            ->assertSee('Language-specific records')
            ->assertSee('Privacy Policy')
            ->assertSee('/privacy-policy')
            ->assertSee('English')
            ->assertDontSee('The route, authorization boundary');

        $this->assertSame('privacy-policy', $page->slug);
    }

    public function test_page_language_is_required_and_errors_are_accessible(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();

        $this->actingAs($admin)
            ->followingRedirects()
            ->from(route('admin.page-studio.create'))
            ->post(route('admin.page-studio.store'), [
                'title' => 'Contact',
                'slug' => 'contact',
                'page_type' => 'contact',
                'status' => 'draft',
                'content_readiness' => 'outline',
            ])
            ->assertOk()
            ->assertSee('Please fix the highlighted fields.')
            ->assertSee('page-errors-title')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('role="alert"', false);
    }

    public function test_page_editor_create_and_edit_render_with_media_picker_inside_admin_shell(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $asset = $this->seedAsset($admin);
        $page = Page::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'title' => 'About Temp Mail',
            'slug' => 'about-temp-mail',
            'featured_media_id' => $asset->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.page-studio.create'))
            ->assertOk()
            ->assertSee('Page editor')
            ->assertSee('Publish panel')
            ->assertSee('Choose media')
            ->assertSee('Content editor')
            ->assertDontSee('page_translations');

        $this->actingAs($admin)
            ->get(route('admin.page-studio.edit', $page))
            ->assertOk()
            ->assertSee('About Temp Mail')
            ->assertSee('Picker Hero')
            ->assertSee('Save draft')
            ->assertSee('Publish')
            ->assertSee('Hide');
    }

    public function test_slugs_are_safe_and_unique_per_language(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $german = Locale::query()->where('locale', 'de')->firstOrFail();

        Page::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'title' => 'Terms',
            'slug' => 'terms',
        ]);

        $payload = [
            'locale_id' => $english->id,
            'title' => 'Terms Duplicate',
            'slug' => 'terms',
            'page_type' => 'terms_of_service',
            'status' => 'draft',
            'content_readiness' => 'outline',
        ];

        $this->actingAs($admin)
            ->from(route('admin.page-studio.create'))
            ->post(route('admin.page-studio.store'), $payload)
            ->assertRedirect()
            ->assertSessionHasErrors('slug');

        $this->actingAs($admin)
            ->post(route('admin.page-studio.store'), [
                ...$payload,
                'locale_id' => $german->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pages', [
            'locale_id' => $german->id,
            'slug' => 'terms',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.page-studio.create'))
            ->post(route('admin.page-studio.store'), [
                ...$payload,
                'slug' => 'unsafe/slug',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('slug');
    }

    public function test_blank_slug_is_generated_from_title_and_create_is_audited(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.page-studio.store'), [
                'locale_id' => $english->id,
                'title' => 'API Docs Readiness',
                'slug' => '',
                'page_type' => 'api_docs_readiness',
                'status' => 'draft',
                'content_readiness' => 'needs_content',
                'excerpt' => 'API page foundation.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pages', [
            'locale_id' => $english->id,
            'title' => 'API Docs Readiness',
            'slug' => 'api-docs-readiness',
            'page_type' => 'api_docs_readiness',
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'page.created', 'actor_id' => $admin->id]);
    }

    public function test_language_specific_page_save_persists_content_publish_intent_and_media_usage(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $asset = $this->seedAsset($admin);

        $response = $this->actingAs($admin)
            ->post(route('admin.page-studio.store'), [
                'locale_id' => $english->id,
                'title' => 'English Help Center',
                'slug' => '',
                'page_type' => 'faq_readiness',
                'status' => 'draft',
                'intent' => 'publish',
                'content_readiness' => 'ready',
                'excerpt' => 'Help for temporary inbox users.',
                'content' => 'Use this page for the English help center.',
                'featured_media_id' => $asset->id,
            ]);

        $response->assertRedirect();

        $page = Page::query()->where('slug', 'english-help-center')->firstOrFail();

        $this->assertSame('published', $page->status);
        $this->assertNotNull($page->published_at);
        $this->assertSame('Use this page for the English help center.', $page->content);
        $this->assertDatabaseHas('media_usages', [
            'media_asset_id' => $asset->id,
            'module' => 'pages',
            'usage_context' => 'page_studio',
            'slot' => 'featured_media_id',
            'usable_type' => Page::class,
            'usable_id' => (string) $page->id,
        ]);
    }

    public function test_update_page_can_hide_and_preserves_accessible_field_errors(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $page = Page::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($admin)
            ->put(route('admin.page-studio.update', $page), [
                'locale_id' => $english->id,
                'title' => 'Hidden Knowledge Base',
                'slug' => 'hidden-knowledge-base',
                'page_type' => 'faq_readiness',
                'status' => 'published',
                'intent' => 'hide',
                'content_readiness' => 'needs_review',
                'content' => 'Hidden while content is reviewed.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'status' => 'hidden',
            'slug' => 'hidden-knowledge-base',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->from(route('admin.page-studio.edit', $page))
            ->put(route('admin.page-studio.update', $page), [
                'locale_id' => $english->id,
                'title' => '',
                'slug' => 'bad/slug',
                'page_type' => 'faq_readiness',
                'status' => 'draft',
                'content_readiness' => 'outline',
            ])
            ->assertOk()
            ->assertSee('Please fix the highlighted fields.')
            ->assertSee('page-title-error')
            ->assertSee('page-slug-error')
            ->assertSee('aria-invalid="true"', false);
    }

    public function test_page_filters_by_language_status_type_author_and_search(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $german = Locale::query()->where('locale', 'de')->firstOrFail();

        Page::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'title' => 'English Contact',
            'slug' => 'english-contact',
            'page_type' => 'contact',
            'status' => 'published',
        ]);
        Page::factory()->create([
            'locale_id' => $german->id,
            'author_id' => $admin->id,
            'title' => 'German Privacy',
            'slug' => 'german-privacy',
            'page_type' => 'privacy_policy',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.page-studio.index', [
                'q' => 'contact',
                'locale_id' => $english->id,
                'page_type' => 'contact',
                'status' => 'published',
                'author_id' => $admin->id,
            ]))
            ->assertOk()
            ->assertSee('English Contact')
            ->assertSee('1 records');
    }

    public function test_page_lifecycle_trash_restore_and_delete_are_audited(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $asset = $this->seedAsset($admin);
        $page = Page::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'featured_media_id' => $asset->id,
            'status' => 'published',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.page-studio.trash', $page), ['confirm_trash' => '1'])
            ->assertRedirect(route('admin.page-studio.index', ['status' => 'trashed']));

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'status' => 'trashed']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'page.trashed', 'actor_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.page-studio.restore', $page))
            ->assertRedirect(route('admin.page-studio.edit', $page));

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'status' => 'draft', 'trashed_at' => null]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'page.restored', 'actor_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.page-studio.trash', $page), ['confirm_trash' => '1'])
            ->assertRedirect();

        $this->actingAs($admin)
            ->delete(route('admin.page-studio.destroy', $page), ['confirm_delete' => '1'])
            ->assertRedirect(route('admin.page-studio.index', ['status' => 'trashed']));

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
        $this->assertDatabaseMissing('media_usages', [
            'usable_type' => Page::class,
            'usable_id' => (string) $page->id,
            'slot' => 'featured_media_id',
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'page.permanently_deleted', 'actor_id' => $admin->id]);
    }

    public function test_trash_filter_hides_trashed_pages_until_requested(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();

        Page::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'title' => 'Visible Policy',
            'slug' => 'visible-policy',
            'status' => 'draft',
        ]);
        Page::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'title' => 'Trashed Policy',
            'slug' => 'trashed-policy',
            'status' => 'trashed',
            'trashed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.page-studio.index'))
            ->assertOk()
            ->assertSee('Visible Policy')
            ->assertDontSee('Trashed Policy');

        $response = $this->actingAs($admin)
            ->get(route('admin.page-studio.index').'?status=trashed');

        $trashedPages = app(PageSearchService::class)->search(['status' => 'trashed']);

        $this->assertTrue($trashedPages->getCollection()->contains('title', 'Trashed Policy'));

        $response->assertOk()
            ->assertViewHas('filters', fn (array $filters): bool => ($filters['status'] ?? null) === 'trashed')
            ->assertSee('Viewing trash');
    }

    public function test_signed_preview_requires_signature_and_renders_page_safely(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $page = Page::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'title' => 'Draft Preview',
            'slug' => 'draft-preview',
            'status' => 'draft',
            'content' => 'Private preview content.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.page-studio.preview', $page))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(app(PagePreviewService::class)->previewUrl($page))
            ->assertOk()
            ->assertSee('Signed preview')
            ->assertSee('Private preview content.')
            ->assertSee('Temporary Page Studio preview');
    }

    public function test_legal_page_readiness_uses_settings_mapping_without_owning_settings_ui(): void
    {
        $owner = User::factory()->owner()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $page = Page::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $owner->id,
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'page_type' => 'privacy_policy',
            'status' => 'published',
        ]);

        app(SettingsStore::class)->put('legal', ['privacy_page_id' => $page->id], $owner);

        $this->actingAs($owner)
            ->get(route('admin.page-studio.edit', $page))
            ->assertOk()
            ->assertSee('Legal mapping readiness')
            ->assertSee('Privacy Policy mapped')
            ->assertSee(route('admin.settings.index', ['group' => 'legal']), false)
            ->assertDontSee('SEO title');
    }

    public function test_author_cannot_preview_or_delete_pages(): void
    {
        $author = User::factory()->author()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $page = Page::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $author->id,
            'status' => 'trashed',
            'trashed_at' => now(),
        ]);

        $this->actingAs($author)
            ->get(app(PagePreviewService::class)->previewUrl($page))
            ->assertForbidden();

        $this->actingAs($author)
            ->delete(route('admin.page-studio.destroy', $page), ['confirm_delete' => '1'])
            ->assertForbidden();
    }

    public function test_no_page_translation_tables_or_relationships_are_created(): void
    {
        $this->assertFalse(Schema::hasTable('page_translations'));
        $this->assertFalse(method_exists(Page::class, 'translations'));
    }

    public function test_featured_media_field_has_safe_fallback_when_media_library_is_unavailable(): void
    {
        view()->share('errors', new ViewErrorBag);

        $html = Blade::render('<x-pages.featured-media-field :media-library-ready="false" fallback-value="12" />');

        $this->assertStringContainsString('Featured media', $html);
        $this->assertStringContainsString('Media Library is not available yet', $html);
        $this->assertStringNotContainsString('Choose media', $html);
        $this->assertStringNotContainsString('file path', $html);
    }

    public function test_page_editor_sources_do_not_use_livewire_cdn_alpine_or_hardcoded_localhost(): void
    {
        $files = [
            resource_path('views/dashboard/page-studio/create.blade.php'),
            resource_path('views/dashboard/page-studio/edit.blade.php'),
            resource_path('views/components/pages/page-editor.blade.php'),
            resource_path('views/components/pages/publish-panel.blade.php'),
            resource_path('views/components/pages/featured-media-field.blade.php'),
            resource_path('views/components/pages/lifecycle-actions.blade.php'),
            resource_path('views/components/pages/page-url-panel.blade.php'),
            resource_path('views/dashboard/page-studio/preview.blade.php'),
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file) ?: '';

            $this->assertStringNotContainsString('Livewire', $contents, $file);
            $this->assertStringNotContainsString('livewire', $contents, $file);
            $this->assertStringNotContainsString('cdn.tailwindcss', $contents, $file);
            $this->assertStringNotContainsString('unpkg.com/alpine', $contents, $file);
            $this->assertStringNotContainsString('127.0.0.1', $contents, $file);
        }
    }

    public function test_editor_can_create_but_author_can_only_view_page_studio(): void
    {
        app(LocaleSettingsStore::class)->ensureSeeded();
        $editor = User::factory()->editor()->create();
        $author = User::factory()->author()->create();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();

        $this->actingAs($editor)
            ->post(route('admin.page-studio.store'), [
                'locale_id' => $english->id,
                'title' => 'Editor Contact',
                'page_type' => 'contact',
                'status' => 'draft',
                'content_readiness' => 'outline',
            ])
            ->assertRedirect();

        $this->actingAs($author)
            ->get(route('admin.page-studio.index'))
            ->assertOk();

        $this->actingAs($author)
            ->post(route('admin.page-studio.store'), [
                'locale_id' => $english->id,
                'title' => 'Blocked',
                'page_type' => 'contact',
                'status' => 'draft',
                'content_readiness' => 'outline',
            ])
            ->assertForbidden();
    }

    private function seedAsset(User $uploader): MediaAsset
    {
        $path = 'media/'.Str::uuid().'/picker-hero.png';
        Storage::disk('public')->put($path, 'fake-image');

        return MediaAsset::query()->create([
            'uuid' => (string) Str::uuid(),
            'original_name' => 'picker-hero.png',
            'file_name' => 'picker-hero.png',
            'disk' => 'public',
            'path' => $path,
            'mime_type' => 'image/png',
            'size_bytes' => 1024,
            'width' => 1200,
            'height' => 800,
            'type' => 'image',
            'status' => 'active',
            'title' => 'Picker Hero',
            'alt_text' => 'Picker hero asset',
            'caption' => 'Ready for selection.',
            'uploaded_by' => $uploader->id,
        ]);
    }
}
