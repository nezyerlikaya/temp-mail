<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\Page;
use App\Models\User;
use App\Services\Localization\LocaleSettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PageStudioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
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
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('role="alert"', false);
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

    public function test_no_page_translation_tables_or_relationships_are_created(): void
    {
        $this->assertFalse(Schema::hasTable('page_translations'));
        $this->assertFalse(method_exists(Page::class, 'translations'));
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
}
