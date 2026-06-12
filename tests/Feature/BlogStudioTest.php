<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Locale;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Blog\BlogPostPreviewService;
use App\Services\Blog\BlogPostSearchService;
use App\Services\Localization\LocaleSettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogStudioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_blog_post_create_and_edit_editor_render_inside_admin_shell(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $post = BlogPost::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'title' => 'Inbox privacy editorial',
            'slug' => 'inbox-privacy-editorial',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.blog-studio.create'))
            ->assertOk()
            ->assertSee('Create post')
            ->assertSee('Editorial workspace')
            ->assertSee('Featured image')
            ->assertSee('Publish panel');

        $this->actingAs($admin)
            ->get(route('admin.blog-studio.edit', $post))
            ->assertOk()
            ->assertSee('Inbox privacy editorial')
            ->assertSee('Content editor')
            ->assertSee('Unsaved changes');
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
            ->assertRedirect();

        $this->assertDatabaseHas('blog_posts', [
            'locale_id' => $english->id,
            'title' => 'Temp Mail Product Playbook',
            'slug' => 'temp-mail-product-playbook',
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blog_post.created', 'actor_id' => $admin->id]);
    }

    public function test_blog_post_editor_shows_accessible_validation_errors(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.blog-studio.create'))
            ->post(route('admin.blog-studio.store'), [
                'locale_id' => '',
                'title' => '',
                'slug' => 'unsafe/slug',
                'content_readiness' => 'outline',
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.blog-studio.create'))
            ->assertSessionHasErrors(['locale_id', 'title', 'slug']);

        $content = $this->followingRedirects()
            ->actingAs($admin)
            ->from(route('admin.blog-studio.create'))
            ->post(route('admin.blog-studio.store'), [
                'locale_id' => '',
                'title' => '',
                'slug' => 'unsafe/slug',
                'content_readiness' => 'outline',
                'status' => 'draft',
            ])
            ->assertOk()
            ->getContent();

        $this->assertIsString($content);
        $this->assertStringContainsString('Please fix the highlighted fields.', $content);
        $this->assertStringContainsString('aria-invalid="true"', $content);
        $this->assertStringContainsString('blog-title-error', $content);
    }

    public function test_blog_post_save_persists_language_content_publish_intent_and_media_usage(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $category = BlogCategory::factory()->create(['locale_id' => $english->id, 'name' => 'Privacy', 'slug' => 'privacy']);
        $tag = BlogTag::factory()->create(['locale_id' => $english->id, 'name' => 'Product', 'slug' => 'product']);
        $asset = $this->seedAsset($admin);

        $response = $this->actingAs($admin)
            ->post(route('admin.blog-studio.store'), [
                'locale_id' => $english->id,
                'title' => 'English mailbox privacy guide',
                'slug' => '',
                'excerpt' => 'Privacy tips for disposable inbox users.',
                'content' => 'Use this post for mailbox privacy education.',
                'content_readiness' => 'ready',
                'featured_media_id' => $asset->id,
                'blog_category_id' => $category->id,
                'tag_ids' => [$tag->id],
                'status' => 'draft',
                'intent' => 'publish',
            ]);

        $post = BlogPost::query()->where('slug', 'english-mailbox-privacy-guide')->firstOrFail();

        $response->assertRedirect(route('admin.blog-studio.edit', $post));
        $this->assertSame('published', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertSame('Use this post for mailbox privacy education.', $post->content);
        $this->assertTrue($post->tags()->whereKey($tag->id)->exists());
        $this->assertDatabaseHas('media_usages', [
            'media_asset_id' => $asset->id,
            'module' => 'blog',
            'usage_context' => 'blog_studio',
            'slot' => 'featured_media_id',
            'usable_type' => BlogPost::class,
            'usable_id' => (string) $post->id,
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blog_post.published', 'actor_id' => $admin->id]);
    }

    public function test_blog_post_edit_updates_media_usage_safely(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $firstAsset = $this->seedAsset($admin, 'first.jpg');
        $secondAsset = $this->seedAsset($admin, 'second.jpg');
        $post = BlogPost::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'title' => 'Media update post',
            'slug' => 'media-update-post',
            'featured_media_id' => $firstAsset->id,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.blog-studio.update', $post), [
                'locale_id' => $english->id,
                'title' => 'Media update post revised',
                'slug' => 'media-update-post',
                'excerpt' => 'Updated excerpt.',
                'content' => 'Updated content.',
                'content_readiness' => 'needs_review',
                'featured_media_id' => $secondAsset->id,
                'blog_category_id' => '',
                'status' => 'draft',
                'intent' => 'save_draft',
            ])
            ->assertRedirect(route('admin.blog-studio.edit', $post));

        $this->assertDatabaseMissing('media_usages', [
            'media_asset_id' => $firstAsset->id,
            'module' => 'blog',
            'usage_context' => 'blog_studio',
            'slot' => 'featured_media_id',
            'usable_type' => BlogPost::class,
            'usable_id' => (string) $post->id,
        ]);
        $this->assertDatabaseHas('media_usages', [
            'media_asset_id' => $secondAsset->id,
            'module' => 'blog',
            'usage_context' => 'blog_studio',
            'slot' => 'featured_media_id',
            'usable_type' => BlogPost::class,
            'usable_id' => (string) $post->id,
        ]);
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

    public function test_blog_post_lifecycle_trash_restore_publish_hide_and_delete_are_audited(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $post = BlogPost::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'status' => 'draft',
            'title' => 'Lifecycle post',
            'slug' => 'lifecycle-post',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.blog-studio.publish', $post))
            ->assertRedirect();

        $this->assertDatabaseHas('blog_posts', ['id' => $post->id, 'status' => 'published']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blog_post.published', 'actor_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.blog-studio.hide', $post))
            ->assertRedirect();

        $this->assertDatabaseHas('blog_posts', ['id' => $post->id, 'status' => 'hidden']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blog_post.hidden', 'actor_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.blog-studio.trash', $post), ['confirm_trash' => '1'])
            ->assertRedirect(route('admin.blog-studio.index', ['status' => 'trashed']));

        $this->assertDatabaseHas('blog_posts', ['id' => $post->id, 'status' => 'trashed']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blog_post.trashed', 'actor_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.blog-studio.restore', $post))
            ->assertRedirect();

        $this->assertDatabaseHas('blog_posts', ['id' => $post->id, 'status' => 'draft', 'trashed_at' => null]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blog_post.restored', 'actor_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.blog-studio.trash', $post), ['confirm_trash' => '1']);

        $this->actingAs($admin)
            ->delete(route('admin.blog-studio.destroy', $post), ['confirm_delete' => '1'])
            ->assertRedirect(route('admin.blog-studio.index', ['status' => 'trashed']));

        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blog_post.permanently_deleted', 'actor_id' => $admin->id]);
    }

    public function test_blog_trash_filter_preview_and_status_transitions_are_safe(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $draft = BlogPost::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'title' => 'Private draft preview',
            'slug' => 'private-draft-preview',
            'content' => 'Private blog preview content.',
            'status' => 'draft',
        ]);
        BlogPost::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $admin->id,
            'title' => 'Trashed blog post',
            'slug' => 'trashed-blog-post',
            'status' => 'trashed',
            'trashed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.blog-studio.preview', $draft))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(app(BlogPostPreviewService::class)->previewUrl($draft))
            ->assertOk()
            ->assertSee('Signed preview')
            ->assertSee('Private blog preview content.')
            ->assertSee('Temporary Blog Studio preview');

        $this->actingAs($admin)
            ->get(route('admin.blog-studio.index', ['status' => 'trashed']))
            ->assertOk()
            ->assertViewHas('filters', fn (array $filters): bool => ($filters['status'] ?? null) === 'trashed')
            ->assertViewHas('posts', fn ($posts): bool => $posts->getCollection()->contains('title', 'Trashed blog post')
                && ! $posts->getCollection()->contains('title', 'Private draft preview'))
            ->assertSee('Viewing trash')
            ->assertSee('Trashed blog post');

        $this->actingAs($admin)
            ->post(route('admin.blog-studio.restore', $draft))
            ->assertSessionHasErrors('status');
    }

    public function test_blog_scheduled_publish_readiness_uses_future_publish_date(): void
    {
        $admin = User::factory()->admin()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $future = now()->addDays(3)->format('Y-m-d\TH:i');

        $this->actingAs($admin)
            ->post(route('admin.blog-studio.store'), [
                'locale_id' => $english->id,
                'title' => 'Scheduled mailbox launch',
                'slug' => '',
                'content_readiness' => 'ready',
                'status' => 'draft',
                'intent' => 'publish',
                'published_at' => $future,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('blog_posts', [
            'slug' => 'scheduled-mailbox-launch',
            'status' => 'scheduled',
        ]);
    }

    public function test_author_can_preview_but_cannot_delete_blog_posts(): void
    {
        $author = User::factory()->author()->create();
        app(LocaleSettingsStore::class)->ensureSeeded();
        $english = Locale::query()->where('locale', 'en')->firstOrFail();
        $post = BlogPost::factory()->create([
            'locale_id' => $english->id,
            'author_id' => $author->id,
            'status' => 'trashed',
            'trashed_at' => now(),
        ]);

        $this->actingAs($author)
            ->get(app(BlogPostPreviewService::class)->previewUrl($post))
            ->assertOk();

        $this->actingAs($author)
            ->delete(route('admin.blog-studio.destroy', $post), ['confirm_delete' => '1'])
            ->assertForbidden();
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
            app_path('Services/Blog/BlogPostLifecycleService.php'),
            app_path('Services/Blog/BlogPostPreviewService.php'),
            app_path('Actions/Blog/TrashBlogPostAction.php'),
            app_path('Actions/Blog/RestoreBlogPostAction.php'),
            app_path('Actions/Blog/DeleteBlogPostAction.php'),
            resource_path('views/dashboard/blog-studio/index.blade.php'),
            resource_path('views/dashboard/blog-studio/preview.blade.php'),
            resource_path('views/components/blog/filter-bar.blade.php'),
            resource_path('views/components/blog/post-card.blade.php'),
            resource_path('views/components/blog/post-row.blade.php'),
            resource_path('views/components/blog/post-editor.blade.php'),
            resource_path('views/components/blog/publish-panel.blade.php'),
            resource_path('views/components/blog/lifecycle-actions.blade.php'),
            resource_path('views/components/blog/public-url-panel.blade.php'),
            resource_path('views/components/blog/delete-warning.blade.php'),
            resource_path('views/dashboard/blog-studio/create.blade.php'),
            resource_path('views/dashboard/blog-studio/edit.blade.php'),
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

    private function seedAsset(User $admin, string $name = 'hero.jpg'): MediaAsset
    {
        return MediaAsset::query()->create([
            'uuid' => (string) str()->uuid(),
            'disk' => 'public',
            'path' => 'media/'.$name,
            'directory' => 'media',
            'file_name' => $name,
            'original_name' => $name,
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 1200,
            'type' => 'image',
            'title' => str($name)->before('.')->headline()->toString(),
            'alt_text' => 'Blog featured image',
            'status' => 'active',
            'uploaded_by' => $admin->id,
        ]);
    }
}
