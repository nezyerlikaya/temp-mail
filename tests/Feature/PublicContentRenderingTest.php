<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Locale;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\SeoRecord;
use App\Models\ThemeState;
use App\Models\User;
use App\Services\Installer\InstallState;
use App\Services\Settings\SettingsStore;
use App\Services\Themes\ThemeCacheService;
use App\Services\Translations\TranslationStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicContentRenderingTest extends TestCase
{
    use RefreshDatabase;

    private bool $hadInstallLock;

    private ?string $originalInstallLock;

    private Locale $english;

    private Locale $arabic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
        $lockPath = app(InstallState::class)->lockPath();
        $this->hadInstallLock = File::exists($lockPath);
        $this->originalInstallLock = $this->hadInstallLock ? File::get($lockPath) : null;
        app(InstallState::class)->lock();
        app(TranslationStore::class)->syncRegistry();
        $this->english = $this->locale('en', 'English', 'ltr', true);
        $this->arabic = $this->locale('ar', 'Arabic', 'rtl');
        $this->activateTheme('horizon');
    }

    protected function tearDown(): void
    {
        $lockPath = app(InstallState::class)->lockPath();
        if ($this->hadInstallLock) {
            File::put($lockPath, $this->originalInstallLock ?? '');
        } else {
            File::delete($lockPath);
        }

        parent::tearDown();
    }

    public function test_blog_index_and_post_render_only_published_same_locale_content(): void
    {
        $published = $this->createPost($this->english, ['title' => 'Privacy Guide', 'slug' => 'privacy-guide']);
        $this->createPost($this->english, ['title' => 'Draft Guide', 'slug' => 'draft-guide', 'status' => 'draft', 'published_at' => null]);
        $this->createPost($this->arabic, ['title' => 'Arabic Guide', 'slug' => 'arabic-guide']);

        $this->get(route('public.blog.index', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('Privacy Guide')
            ->assertDontSee('Draft Guide')
            ->assertDontSee('Arabic Guide');

        $this->get(route('public.blog.show', ['locale' => 'en', 'slug' => $published->slug]))
            ->assertOk()
            ->assertSee('Privacy Guide')
            ->assertSee('Safe article content')
            ->assertDontSee('alert(1)', false);

        $this->get(route('public.blog.show', ['locale' => 'ar', 'slug' => $published->slug]))->assertNotFound();
        $this->get(route('public.blog.show', ['locale' => 'en', 'slug' => 'draft-guide']))->assertNotFound();
    }

    public function test_category_tag_author_archives_are_locale_isolated_and_paginated(): void
    {
        $author = User::factory()->admin()->create([
            'display_name' => 'Editorial Team',
            'public_author_slug' => 'editorial-team',
            'author_profile_active' => true,
            'author_bio' => 'Privacy-first publishing.',
        ]);
        $category = BlogCategory::factory()->create(['locale_id' => $this->english->id, 'name' => 'Privacy', 'slug' => 'privacy']);
        $tag = BlogTag::factory()->create(['locale_id' => $this->english->id, 'name' => 'Security', 'slug' => 'security']);
        $post = $this->createPost($this->english, ['blog_category_id' => $category->id, 'author_id' => $author->id]);
        $post->tags()->attach($tag);

        $this->get(route('public.blog.category', ['locale' => 'en', 'slug' => 'privacy']))->assertOk()->assertSee($post->title);
        $this->get(route('public.blog.tag', ['locale' => 'en', 'slug' => 'security']))->assertOk()->assertSee($post->title);
        $this->get(route('public.blog.author', ['locale' => 'en', 'slug' => 'editorial-team']))->assertOk()->assertSee('Editorial Team');
        $this->get(route('public.blog.category', ['locale' => 'ar', 'slug' => 'privacy']))->assertNotFound();
    }

    public function test_locale_switching_for_content_routes_falls_back_to_relevant_index_without_inventing_translation(): void
    {
        $post = $this->createPost($this->english, ['slug' => 'only-english']);

        $this->get(route('public.blog.show', ['locale' => 'en', 'slug' => $post->slug]))
            ->assertOk()
            ->assertSee(route('public.blog.index', ['locale' => 'ar']), false)
            ->assertDontSee(route('public.blog.show', ['locale' => 'ar', 'slug' => $post->slug]), false);
    }

    public function test_page_visibility_legal_mapping_media_and_rtl_rendering_work(): void
    {
        $admin = User::factory()->admin()->create();
        $media = MediaAsset::query()->create([
            'uuid' => fake()->uuid(),
            'original_name' => 'privacy.jpg',
            'file_name' => 'privacy.jpg',
            'disk' => 'public',
            'path' => 'media/privacy.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 1200,
            'width' => 1200,
            'height' => 675,
            'type' => 'image',
            'status' => 'active',
            'alt_text' => 'Privacy illustration',
            'uploaded_by' => $admin->id,
        ]);
        $page = Page::factory()->create([
            'locale_id' => $this->arabic->id,
            'title' => 'Privacy Policy',
            'slug' => 'privacy',
            'page_type' => 'privacy_policy',
            'status' => 'published',
            'published_at' => now(),
            'content' => '<p>Private page content</p>',
            'featured_media_id' => $media->id,
        ]);
        app(SettingsStore::class)->put('legal', ['privacy_page_id' => $page->id], $admin);

        $this->get(route('public.pages.show', ['locale' => 'ar', 'slug' => 'privacy']))
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('Privacy illustration')
            ->assertSee(Storage::disk('public')->url('media/privacy.jpg'), false);

        $this->get(route('public.pages.show', ['locale' => 'en', 'slug' => 'privacy']))->assertNotFound();
    }

    public function test_seo_canonical_hreflang_schema_and_sitemap_exclusions_are_safe(): void
    {
        $post = $this->createPost($this->english, ['title' => 'SEO Article', 'slug' => 'seo-article']);
        $excluded = $this->createPost($this->english, ['title' => 'Hidden From Sitemap', 'slug' => 'hidden-sitemap']);
        SeoRecord::query()->create([
            'locale_id' => $this->english->id,
            'target_type' => 'blog_post',
            'target_key' => 'blog-post:'.$post->id,
            'meta_title' => 'Manual SEO Title',
            'meta_description' => 'Manual SEO description.',
            'robots_index' => true,
            'robots_follow' => true,
            'include_in_sitemap' => true,
            'sitemap_priority' => 0.7,
            'sitemap_change_frequency' => 'weekly',
            'twitter_card' => 'summary_large_image',
            'schema_type' => 'Article',
            'schema_json' => ['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => 'SEO Article'],
        ]);
        SeoRecord::query()->create([
            'locale_id' => $this->english->id,
            'target_type' => 'blog_post',
            'target_key' => 'blog-post:'.$excluded->id,
            'robots_index' => false,
            'robots_follow' => true,
            'include_in_sitemap' => false,
            'sitemap_priority' => 0.5,
            'sitemap_change_frequency' => 'weekly',
            'twitter_card' => 'summary',
        ]);

        $canonical = route('public.blog.show', ['locale' => 'en', 'slug' => $post->slug]);
        $this->get($canonical)
            ->assertOk()
            ->assertSee('<title>Manual SEO Title</title>', false)
            ->assertSee('<link rel="canonical" href="'.$canonical.'">', false)
            ->assertSee('hreflang="en"', false)
            ->assertDontSee('hreflang="ar"', false)
            ->assertSee('"@type":"Article"', false);

        $this->get(route('public.sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee($canonical, false)
            ->assertDontSee(route('public.blog.show', ['locale' => 'en', 'slug' => $excluded->slug]), false);
    }

    public function test_comment_form_uses_existing_submission_flow_only_when_available(): void
    {
        $post = $this->createPost($this->english, ['comments_enabled' => true]);

        $this->get(route('public.blog.show', ['locale' => 'en', 'slug' => $post->slug]))
            ->assertOk()
            ->assertSee(route('public.blog.comments.store', ['locale' => 'en', 'post' => $post]), false);

        $this->post(route('public.blog.comments.store', ['locale' => 'en', 'post' => $post]), [
            'author_name' => 'Reader',
            'author_email' => 'reader@example.test',
            'content' => 'Useful privacy guide.',
        ])->assertRedirect();

        $this->assertDatabaseHas('comments', ['blog_post_id' => $post->id, 'author_name' => 'Reader']);
    }

    public function test_horizon_atlas_and_legacy_render_blog_and_pages_without_livewire_or_hardcoded_assets(): void
    {
        $post = $this->createPost($this->english);

        foreach (['horizon', 'atlas', 'legacy'] as $theme) {
            $this->activateTheme($theme);
            $this->get(route('public.blog.show', ['locale' => 'en', 'slug' => $post->slug]))
                ->assertOk()
                ->assertSee('data-public-theme="'.$theme.'"', false);
        }

        foreach (File::allFiles(resource_path('views/themes')) as $file) {
            $contents = File::get($file->getPathname());
            $this->assertStringNotContainsString('Livewire', $contents);
            $this->assertStringNotContainsString('127.0.0.1', $contents);
            $this->assertStringNotContainsString('cdn.tailwindcss', $contents);
        }
    }

    private function locale(string $code, string $name, string $direction, bool $default = false): Locale
    {
        return Locale::query()->create([
            'language_name' => $name,
            'native_name' => $name,
            'locale' => $code,
            'direction' => $direction,
            'region' => 'Global',
            'market_readiness' => 'ready',
            'is_active' => true,
            'is_default' => $default,
            'sort_order' => $default ? 1 : 2,
            'launch_status' => 'launched',
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function createPost(Locale $locale, array $overrides = []): BlogPost
    {
        return BlogPost::factory()->create([
            'locale_id' => $locale->id,
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'content' => '<p>Safe article content</p><script>alert(1)</script>',
            'content_readiness' => 'ready',
            ...$overrides,
        ]);
    }

    private function activateTheme(string $slug): void
    {
        ThemeState::query()->delete();
        foreach (['horizon', 'atlas', 'legacy'] as $theme) {
            ThemeState::query()->create([
                'slug' => $theme,
                'status' => $theme === $slug ? 'active' : 'inactive',
                'last_activated_at' => $theme === $slug ? now() : null,
            ]);
        }
        app(ThemeCacheService::class)->clear();
    }
}
