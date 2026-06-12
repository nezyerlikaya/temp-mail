<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Locale;
use App\Models\User;
use App\Services\Blog\BlogPostSearchService;
use App\Services\Localization\LocaleSettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BlogStudioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_blog_studio_renders_inside_admin_shell_and_replaces_placeholder(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $category = BlogCategory::factory()->create([
            'locale_id' => $english->id,
            'name' => 'Inbox Strategy',
            'slug' => 'inbox-strategy',
        ]);

        BlogPost::factory()->create([
            'locale_id' => $english->id,
            'blog_category_id' => $category->id,
            'author_id' => $admin->id,
            'title' => 'Disposable inbox launch guide',
            'slug' => 'disposable-inbox-launch-guide',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.blog-studio.index'))
            ->assertOk()
            ->assertSee('Blog Studio')
            ->assertSee('Disposable inbox launch guide')
            ->assertSee('Inbox Strategy')
            ->assertDontSee('This workspace is ready for implementation.');
    }

    public function test_blog_post_filters_search_language_status_category_author_and_date(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $german = Locale::query()->where('locale', 'de')->firstOrFail();
        $category = BlogCategory::factory()->create(['locale_id' => $english->id, 'name' => 'Product', 'slug' => 'product']);

        BlogPost::factory()->create([
            'locale_id' => $english->id,
            'blog_category_id' => $category->id,
            'author_id' => $admin->id,
            'title' => 'Mailbox growth notes',
            'slug' => 'mailbox-growth-notes',
            'status' => 'published',
            'created_at' => now(),
        ]);
        BlogPost::factory()->create([
            'locale_id' => $german->id,
            'author_id' => $other->id,
            'title' => 'German release notes',
            'slug' => 'german-release-notes',
            'status' => 'draft',
            'created_at' => now()->subMonth(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.blog-studio.index', [
                'q' => 'mailbox',
                'locale_id' => $english->id,
                'status' => 'published',
                'category_id' => $category->id,
                'author_id' => $admin->id,
                'date' => 'week',
            ]));

        $response->assertOk()
            ->assertViewHas('posts', fn ($posts): bool => $posts->getCollection()->contains('title', 'Mailbox growth notes')
                && ! $posts->getCollection()->contains('title', 'German release notes'))
            ->assertSee('Mailbox growth notes');
    }

    public function test_blog_post_creation_requires_language_and_generates_safe_locale_slug(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();

        $payload = [
            'title' => 'Temp Mail Product Playbook',
            'slug' => '',
            'content_readiness' => 'outline',
            'status' => 'draft',
        ];

        $this->actingAs($admin)
            ->from(route('admin.blog-studio.index'))
            ->post(route('admin.blog-studio.store'), $payload)
            ->assertRedirect()
            ->assertSessionHasErrors('locale_id');

        $this->actingAs($admin)
            ->post(route('admin.blog-studio.store'), [
                ...$payload,
                'locale_id' => $english->id,
            ])
            ->assertRedirect(route('admin.blog-studio.index'));

        $this->assertDatabaseHas('blog_posts', [
            'locale_id' => $english->id,
            'title' => 'Temp Mail Product Playbook',
            'slug' => 'temp-mail-product-playbook',
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blog_post.created', 'actor_id' => $admin->id]);
    }

    public function test_blog_slug_is_unique_per_language_not_translation_linked(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $spanish = Locale::query()->where('locale', 'es')->firstOrFail();

        BlogPost::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'slug' => 'privacy-for-temp-mail',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.blog-studio.store'), [
                'locale_id' => $spanish->id,
                'title' => 'Privacy for Temp Mail',
                'slug' => 'privacy-for-temp-mail',
                'content_readiness' => 'outline',
                'status' => 'draft',
            ])
            ->assertRedirect();

        $this->assertSame(2, BlogPost::query()->where('slug', 'privacy-for-temp-mail')->count());
        $this->assertFalse(Schema::hasTable('post_translations'));
    }

    public function test_category_and_tag_relationships_must_match_post_language(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $french = Locale::query()->where('locale', 'fr')->firstOrFail();
        $foreignCategory = BlogCategory::factory()->create(['locale_id' => $french->id]);
        $foreignTag = BlogTag::factory()->create(['locale_id' => $french->id]);

        $this->actingAs($admin)
            ->from(route('admin.blog-studio.index'))
            ->post(route('admin.blog-studio.store'), [
                'locale_id' => $english->id,
                'title' => 'Localized category check',
                'slug' => 'localized-category-check',
                'content_readiness' => 'outline',
                'status' => 'draft',
                'blog_category_id' => $foreignCategory->id,
                'tag_ids' => [$foreignTag->id],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['blog_category_id', 'tag_ids.0']);
    }

    public function test_blog_search_service_excludes_trashed_posts_by_default(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();

        BlogPost::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'title' => 'Visible editorial plan',
            'slug' => 'visible-editorial-plan',
            'status' => 'draft',
        ]);
        BlogPost::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'title' => 'Trashed editorial plan',
            'slug' => 'trashed-editorial-plan',
            'status' => 'trashed',
            'trashed_at' => now(),
        ]);

        $default = app(BlogPostSearchService::class)->search(['status' => 'all']);
        $trashed = app(BlogPostSearchService::class)->search(['status' => 'trashed']);

        $this->assertTrue($default->getCollection()->contains('title', 'Visible editorial plan'));
        $this->assertFalse($default->getCollection()->contains('title', 'Trashed editorial plan'));
        $this->assertTrue($trashed->getCollection()->contains('title', 'Trashed editorial plan'));
    }

    public function test_blog_studio_sources_do_not_use_forbidden_patterns(): void
    {
        $files = [
            app_path('Http/Controllers/Admin/BlogStudioController.php'),
            app_path('Models/BlogPost.php'),
            app_path('Models/BlogCategory.php'),
            app_path('Models/BlogTag.php'),
            app_path('Services/Blog/BlogPostStore.php'),
            app_path('Services/Blog/BlogPostSearchService.php'),
            app_path('Services/Blog/BlogSlugService.php'),
            resource_path('views/dashboard/blog-studio/index.blade.php'),
            resource_path('views/components/blog/filter-bar.blade.php'),
            resource_path('views/components/blog/post-card.blade.php'),
            resource_path('views/components/blog/post-row.blade.php'),
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            $this->assertIsString($contents);
            $this->assertStringNotContainsString('Livewire', $contents, $file);
            $this->assertStringNotContainsString('livewire', $contents, $file);
            $this->assertStringNotContainsString('cdn.tailwindcss.com', $contents, $file);
            $this->assertStringNotContainsString('unpkg.com/alpine', $contents, $file);
            $this->assertStringNotContainsString('127.0.0.1', $contents, $file);
            $this->assertStringNotContainsString('post_translations', $contents, $file);
        }
    }
}
