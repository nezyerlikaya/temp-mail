<?php

namespace Tests\Feature;

use App\Actions\Blog\AttachPostTaxonomyAction;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Locale;
use App\Models\User;
use App\Services\Localization\LocaleSettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BlogTaxonomyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_taxonomy_page_renders_categories_and_tags_inside_admin_shell(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');

        BlogCategory::factory()->create([
            'locale_id' => $english->id,
            'name' => 'Inbox Strategy',
            'slug' => 'inbox-strategy',
        ]);
        BlogTag::factory()->create([
            'locale_id' => $english->id,
            'name' => 'Deliverability',
            'slug' => 'deliverability',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.taxonomy.index'))
            ->assertOk()
            ->assertSee('Taxonomy')
            ->assertSee('Inbox Strategy')
            ->assertSee('Create category')
            ->assertDontSee('This workspace is ready for implementation.');

        $this->actingAs($admin)
            ->get(route('admin.taxonomy.index', ['tab' => 'tags']))
            ->assertOk()
            ->assertSee('Deliverability')
            ->assertSee('Create tag');
    }

    public function test_category_create_update_and_counts_are_persisted(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');

        $this->actingAs($admin)
            ->post(route('admin.taxonomy.categories.store'), [
                'locale_id' => $english->id,
                'name' => 'Privacy Playbooks',
                'slug' => '',
                'description' => 'Editorial grouping for privacy guides.',
                'status' => 'active',
                'sort_order' => 4,
            ])
            ->assertRedirect();

        $category = BlogCategory::query()->where('slug', 'privacy-playbooks')->firstOrFail();
        BlogPost::factory()->create(['locale_id' => $english->id, 'blog_category_id' => $category->id]);

        $this->assertDatabaseHas('blog_categories', [
            'id' => $category->id,
            'locale_id' => $english->id,
            'status' => 'active',
            'is_active' => true,
            'sort_order' => 4,
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blog_category.created', 'actor_id' => $admin->id]);

        $this->actingAs($admin)
            ->put(route('admin.taxonomy.categories.update', $category), [
                'locale_id' => $english->id,
                'name' => 'Privacy Hub',
                'slug' => 'privacy-hub',
                'description' => 'Updated grouping.',
                'status' => 'hidden',
                'sort_order' => 8,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('blog_categories', [
            'id' => $category->id,
            'name' => 'Privacy Hub',
            'status' => 'hidden',
            'is_active' => false,
            'sort_order' => 8,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.taxonomy.index', ['q' => 'privacy', 'status' => 'hidden']))
            ->assertOk()
            ->assertViewHas('categories', fn ($categories): bool => $categories->getCollection()->contains('posts_count', 1))
            ->assertSee('Privacy Hub');
    }

    public function test_tag_create_update_and_filtering_are_persisted(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');
        $german = $this->locale('de');

        BlogTag::factory()->create([
            'locale_id' => $german->id,
            'name' => 'German Only',
            'slug' => 'german-only',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.taxonomy.tags.store'), [
                'locale_id' => $english->id,
                'name' => 'Inbox Ops',
                'slug' => '',
                'description' => 'Operational tag.',
                'status' => 'active',
            ])
            ->assertRedirect();

        $tag = BlogTag::query()->where('slug', 'inbox-ops')->firstOrFail();
        $post = BlogPost::factory()->create(['locale_id' => $english->id]);
        $post->tags()->sync([$tag->id]);

        $this->actingAs($admin)
            ->put(route('admin.taxonomy.tags.update', $tag), [
                'locale_id' => $english->id,
                'name' => 'Inbox Operations',
                'slug' => 'inbox-operations',
                'description' => 'Updated tag.',
                'status' => 'hidden',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('blog_tags', [
            'id' => $tag->id,
            'name' => 'Inbox Operations',
            'status' => 'hidden',
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'blog_tag.updated', 'actor_id' => $admin->id]);

        $this->actingAs($admin)
            ->get(route('admin.taxonomy.index', ['tab' => 'tags', 'q' => 'operations', 'locale_id' => $english->id, 'status' => 'hidden']))
            ->assertOk()
            ->assertViewHas('tags', fn ($tags): bool => $tags->getCollection()->contains('posts_count', 1)
                && ! $tags->getCollection()->contains('name', 'German Only'))
            ->assertSee('Inbox Operations')
            ->assertDontSee('German Only');
    }

    public function test_taxonomy_validation_shows_error_summary(): void
    {
        $admin = User::factory()->admin()->create();

        $this->followingRedirects()
            ->actingAs($admin)
            ->from(route('admin.taxonomy.index'))
            ->post(route('admin.taxonomy.categories.store'), [
                'locale_id' => '',
                'name' => '',
                'slug' => 'bad/slug',
                'status' => 'active',
            ])
            ->assertOk()
            ->assertSee('Please fix the highlighted fields.')
            ->assertSee('aria-invalid="true"', false);
    }

    public function test_same_language_taxonomy_attach_is_enforced_by_action(): void
    {
        $english = $this->locale('en');
        $french = $this->locale('fr');
        $post = BlogPost::factory()->create(['locale_id' => $english->id]);
        $foreignCategory = BlogCategory::factory()->create(['locale_id' => $french->id]);
        $foreignTag = BlogTag::factory()->create(['locale_id' => $french->id]);

        $this->expectException(ValidationException::class);

        app(AttachPostTaxonomyAction::class)->handle($post, $foreignCategory->id, [$foreignTag->id]);
    }

    public function test_taxonomy_sources_do_not_use_forbidden_patterns_or_translation_tables(): void
    {
        $files = [
            app_path('Http/Controllers/Admin/BlogTaxonomyController.php'),
            app_path('Actions/Blog/CreateCategoryAction.php'),
            app_path('Actions/Blog/UpdateCategoryAction.php'),
            app_path('Actions/Blog/CreateTagAction.php'),
            app_path('Actions/Blog/UpdateTagAction.php'),
            app_path('Actions/Blog/AttachPostTaxonomyAction.php'),
            app_path('Services/Blog/BlogTaxonomyService.php'),
            app_path('Services/Blog/CategorySearchService.php'),
            app_path('Services/Blog/TagSearchService.php'),
            resource_path('views/dashboard/taxonomy/index.blade.php'),
            resource_path('views/components/blog/category-editor.blade.php'),
            resource_path('views/components/blog/tag-editor.blade.php'),
            resource_path('views/components/blog/taxonomy-filter-bar.blade.php'),
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
            $this->assertStringNotContainsString('category_translations', $contents, $file);
            $this->assertStringNotContainsString('tag_translations', $contents, $file);
        }

        $this->assertFalse(Schema::hasTable('post_translations'));
    }

    private function locale(string $code): Locale
    {
        app(LocaleSettingsStore::class)->ensureSeeded();

        return Locale::query()->where('locale', $code)->firstOrFail();
    }
}
