<?php

namespace Tests\Feature;

use App\Actions\Seo\CreateSeoRecordAction;
use App\Models\BlogPost;
use App\Models\Locale;
use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\Page;
use App\Models\SeoRecord;
use App\Models\User;
use App\Services\Localization\LocaleSettingsStore;
use App\Services\Seo\SeoRecordResolver;
use App\Services\Seo\SeoTargetRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class SeoGrowthCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_seo_growth_center_renders_inside_admin_shell_and_replaces_placeholder(): void
    {
        $admin = User::factory()->admin()->create();
        $this->locale('en');

        $this->actingAs($admin)
            ->get(route('admin.seo-growth-center.index'))
            ->assertOk()
            ->assertSee('SEO Growth Center')
            ->assertSee('Search health summary')
            ->assertSee('Target coverage queue')
            ->assertSee('Prepare record')
            ->assertDontSee('This workspace is ready for implementation.');
    }

    public function test_seo_record_data_structure_is_language_and_target_specific(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');
        $german = $this->locale('de');

        $englishRecord = app(CreateSeoRecordAction::class)->handle($admin, $english->id, 'homepage', 'home');
        $germanRecord = app(CreateSeoRecordAction::class)->handle($admin, $german->id, 'homepage', 'home');

        $this->assertNotSame($englishRecord->id, $germanRecord->id);
        $this->assertDatabaseHas('seo_records', [
            'locale_id' => $english->id,
            'target_type' => 'homepage',
            'target_key' => 'home',
            'robots_index' => true,
            'robots_follow' => true,
            'include_in_sitemap' => true,
        ]);
        $this->assertDatabaseHas('seo_records', [
            'locale_id' => $german->id,
            'target_type' => 'homepage',
            'target_key' => 'home',
        ]);
    }

    public function test_seo_target_registry_supports_required_targets_without_translation_relationships(): void
    {
        $english = $this->locale('en');
        Page::factory()->create([
            'locale_id' => $english->id,
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
        ]);
        BlogPost::factory()->create([
            'locale_id' => $english->id,
            'title' => 'Disposable email guide',
            'slug' => 'disposable-email-guide',
        ]);

        $targets = app(SeoTargetRegistry::class)->targets($english);
        $types = $targets->pluck('target_type')->unique()->values()->all();

        foreach ([
            'homepage',
            'temporary_email_generator',
            'disposable_email',
            'ten_minute_mail',
            'inbox',
            'pricing',
            'blog_post',
            'page',
            'language_landing',
        ] as $type) {
            $this->assertContains($type, $types);
        }

        $this->assertFalse(Schema::hasTable('page_translations'));
        $this->assertFalse(Schema::hasTable('post_translations'));
        $this->assertFalse(Schema::hasTable('section_translations'));
    }

    public function test_seo_record_resolver_uses_service_generated_canonical_and_does_not_own_content(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');
        $page = Page::factory()->create([
            'locale_id' => $english->id,
            'title' => 'Terms',
            'slug' => 'terms',
            'content' => 'Page Studio owns this content.',
        ]);

        $record = app(CreateSeoRecordAction::class)->handle($admin, $english->id, 'page', 'page:'.$page->id);
        $resolved = app(SeoRecordResolver::class)->resolve($english, 'page', 'page:'.$page->id);

        $this->assertTrue($record->is($resolved));
        $this->assertStringEndsWith('/en/terms', $record->canonical_url);
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'content' => 'Page Studio owns this content.']);
        $this->assertDatabaseMissing('seo_records', ['meta_description' => 'Page Studio owns this content.']);
    }

    public function test_seo_filters_support_language_target_missing_metadata_robots_and_sitemap(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');
        $german = $this->locale('de');

        SeoRecord::query()->create([
            'locale_id' => $english->id,
            'target_type' => 'homepage',
            'target_key' => 'home',
            'meta_title' => null,
            'meta_description' => null,
            'robots_index' => false,
            'robots_follow' => true,
            'include_in_sitemap' => false,
            'sitemap_priority' => 1.0,
            'sitemap_change_frequency' => 'daily',
            'twitter_card' => 'summary_large_image',
        ]);
        SeoRecord::query()->create([
            'locale_id' => $german->id,
            'target_type' => 'pricing',
            'target_key' => 'pricing',
            'meta_title' => 'Pricing',
            'meta_description' => 'Pricing metadata.',
            'robots_index' => true,
            'robots_follow' => true,
            'include_in_sitemap' => true,
            'sitemap_priority' => 0.8,
            'sitemap_change_frequency' => 'monthly',
            'twitter_card' => 'summary_large_image',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.seo-growth-center.index', [
                'locale' => 'en',
                'target_type' => 'homepage',
                'missing_metadata' => 'missing',
                'robots' => 'noindex',
                'sitemap' => 'excluded',
            ]));

        $response->assertOk()
            ->assertViewHas('records', fn ($records): bool => $records->getCollection()->contains('target_key', 'home')
                && ! $records->getCollection()->contains('target_key', 'pricing'))
            ->assertSee('Meta title pending')
            ->assertDontSee('Pricing metadata.');
    }

    public function test_seo_record_update_validates_foundation_fields_and_is_audited(): void
    {
        $admin = User::factory()->admin()->create();
        $record = SeoRecord::query()->create([
            'locale_id' => $this->locale('en')->id,
            'target_type' => 'homepage',
            'target_key' => 'home',
            'canonical_url' => url('/en'),
            'robots_index' => true,
            'robots_follow' => true,
            'include_in_sitemap' => true,
            'sitemap_priority' => 1.0,
            'sitemap_change_frequency' => 'daily',
            'twitter_card' => 'summary_large_image',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.seo-growth-center.index'))
            ->put(route('admin.seo-growth-center.records.update', $record), [
                'meta_title' => str_repeat('A', 181),
                'robots_index' => '1',
                'robots_follow' => '1',
                'include_in_sitemap' => '1',
                'sitemap_priority' => 1.2,
                'sitemap_change_frequency' => 'daily',
                'twitter_card' => 'summary_large_image',
            ])
            ->assertRedirect(route('admin.seo-growth-center.index'))
            ->assertSessionHasErrors(['meta_title', 'sitemap_priority']);

        $this->actingAs($admin)
            ->put(route('admin.seo-growth-center.records.update', $record), [
                'meta_title' => 'Temp Mail SaaS',
                'meta_description' => 'Create temporary inboxes with language-specific SEO metadata.',
                'canonical_url' => url('/en'),
                'robots_index' => '1',
                'robots_follow' => '1',
                'include_in_sitemap' => '1',
                'sitemap_priority' => 1.0,
                'sitemap_change_frequency' => 'daily',
                'og_title' => 'Temp Mail SaaS',
                'og_description' => 'Social card readiness.',
                'twitter_card' => 'summary_large_image',
                'schema_type' => 'WebSite',
            ])
            ->assertRedirect(route('admin.seo-growth-center.records.edit', $record));

        $this->assertDatabaseHas('seo_records', [
            'id' => $record->id,
            'meta_title' => 'Temp Mail SaaS',
            'updated_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'seo.record_updated', 'actor_id' => $admin->id]);
    }

    public function test_seo_editor_renders_previews_schema_controls_media_picker_and_noindex_warning(): void
    {
        $admin = User::factory()->admin()->create();
        $record = SeoRecord::query()->create([
            'locale_id' => $this->locale('en')->id,
            'target_type' => 'homepage',
            'target_key' => 'home',
            'meta_title' => 'Temp Mail privacy inbox generator for fast disposable email',
            'meta_description' => 'Create private temporary inboxes for safer signups, spam control, and disposable email workflows in seconds.',
            'canonical_url' => url('/en'),
            'robots_index' => false,
            'robots_follow' => true,
            'include_in_sitemap' => true,
            'sitemap_priority' => 1.0,
            'sitemap_change_frequency' => 'daily',
            'twitter_card' => 'summary_large_image',
            'schema_type' => 'WebSite',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.seo-growth-center.records.edit', $record))
            ->assertOk()
            ->assertSee('Google preview')
            ->assertSee('Social preview')
            ->assertSee('Schema JSON-LD')
            ->assertSee('Open Graph image')
            ->assertSee('Noindex is active')
            ->assertSee('You have unsaved SEO changes.');
    }

    public function test_invalid_canonical_and_schema_json_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $record = $this->seoRecord();

        $this->actingAs($admin)
            ->from(route('admin.seo-growth-center.records.edit', $record))
            ->put(route('admin.seo-growth-center.records.update', $record), [
                ...$this->validSeoPayload(),
                'canonical_url' => 'javascript:alert(1)',
            ])
            ->assertRedirect(route('admin.seo-growth-center.records.edit', $record))
            ->assertSessionHasErrors('canonical_url');

        $this->actingAs($admin)
            ->from(route('admin.seo-growth-center.records.edit', $record))
            ->put(route('admin.seo-growth-center.records.update', $record), [
                ...$this->validSeoPayload(),
                'schema_json_text' => '{"@context":"https://schema.org","bad":"<script>alert(1)</script>"}',
            ])
            ->assertRedirect(route('admin.seo-growth-center.records.edit', $record))
            ->assertSessionHasErrors('schema_json_text');
    }

    public function test_seo_media_picker_updates_images_and_media_usage(): void
    {
        $admin = User::factory()->admin()->create();
        $record = $this->seoRecord();
        $og = $this->mediaAsset('og-card.png');
        $twitter = $this->mediaAsset('twitter-card.png');

        $this->actingAs($admin)
            ->put(route('admin.seo-growth-center.records.update', $record), [
                ...$this->validSeoPayload(),
                'og_image_media_id' => $og->id,
                'twitter_image_media_id' => $twitter->id,
            ])
            ->assertRedirect(route('admin.seo-growth-center.records.edit', $record));

        $this->assertDatabaseHas('seo_records', [
            'id' => $record->id,
            'og_image_media_id' => $og->id,
            'twitter_image_media_id' => $twitter->id,
        ]);
        $this->assertDatabaseHas('media_usages', [
            'media_asset_id' => $og->id,
            'module' => 'seo',
            'usage_context' => 'seo_growth_center',
            'slot' => 'og_image_media_id',
            'usable_type' => SeoRecord::class,
            'usable_id' => (string) $record->id,
        ]);
        $this->assertDatabaseHas('media_usages', [
            'media_asset_id' => $twitter->id,
            'slot' => 'twitter_image_media_id',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.seo-growth-center.records.update', $record), [
                ...$this->validSeoPayload(),
                'og_image_media_id' => '',
                'twitter_image_media_id' => '',
            ])
            ->assertRedirect(route('admin.seo-growth-center.records.edit', $record));

        $this->assertSame(0, MediaUsage::query()->where('usable_type', SeoRecord::class)->where('usable_id', (string) $record->id)->count());
    }

    public function test_preview_endpoint_returns_safe_preview_payload_without_persisting(): void
    {
        $admin = User::factory()->admin()->create();
        $record = $this->seoRecord(['meta_title' => 'Old title']);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.seo-growth-center.records.preview', $record), [
                ...$this->validSeoPayload(),
                'meta_title' => 'Preview title for temporary email search result quality',
            ]);

        $response->assertOk()
            ->assertJsonPath('preview.serp.desktop.title', 'Preview title for temporary email search result quality');

        $this->assertDatabaseHas('seo_records', ['id' => $record->id, 'meta_title' => 'Old title']);
    }

    public function test_editor_can_update_seo_but_member_cannot_view_center(): void
    {
        $editor = User::factory()->editor()->create();
        $member = User::factory()->create();
        $this->locale('en');

        $this->actingAs($editor)
            ->get(route('admin.seo-growth-center.index'))
            ->assertOk();

        $this->actingAs($member)
            ->get(route('admin.seo-growth-center.index'))
            ->assertForbidden();
    }

    public function test_seo_growth_center_sources_do_not_use_forbidden_patterns_or_translation_tables(): void
    {
        $files = [
            app_path('Http/Controllers/Admin/SeoGrowthCenterController.php'),
            app_path('Models/SeoRecord.php'),
            app_path('Services/Seo/SeoStore.php'),
            app_path('Services/Seo/SeoTargetRegistry.php'),
            app_path('Services/Seo/SeoRecordResolver.php'),
            app_path('Services/Seo/SeoHealthService.php'),
            app_path('Actions/Seo/CreateSeoRecordAction.php'),
            app_path('Actions/Seo/UpdateSeoRecordAction.php'),
            app_path('Http/Requests/Seo/SeoFilterRequest.php'),
            app_path('Http/Requests/Seo/UpdateSeoRecordRequest.php'),
            app_path('Http/Requests/Seo/PreviewSeoRecordRequest.php'),
            app_path('Services/Seo/SeoEditorService.php'),
            app_path('Services/Seo/SeoPreviewService.php'),
            app_path('Services/Seo/SeoCanonicalValidator.php'),
            app_path('Services/Seo/SeoSchemaValidator.php'),
            app_path('Services/Seo/SeoMediaService.php'),
            resource_path('views/dashboard/seo-growth-center/index.blade.php'),
            resource_path('views/dashboard/seo-growth-center/create.blade.php'),
            resource_path('views/dashboard/seo-growth-center/edit.blade.php'),
            resource_path('views/components/seo/editor.blade.php'),
            resource_path('views/components/seo/target-selector.blade.php'),
            resource_path('views/components/seo/language-selector.blade.php'),
            resource_path('views/components/seo/serp-preview.blade.php'),
            resource_path('views/components/seo/social-preview.blade.php'),
            resource_path('views/components/seo/robots-control.blade.php'),
            resource_path('views/components/seo/sitemap-control.blade.php'),
            resource_path('views/components/seo/schema-editor.blade.php'),
            resource_path('views/components/seo/media-picker-field.blade.php'),
            resource_path('views/components/seo/character-guidance.blade.php'),
            resource_path('views/components/seo/validation-summary.blade.php'),
            resource_path('views/components/seo/metric-card.blade.php'),
            resource_path('views/components/seo/target-row.blade.php'),
            resource_path('views/components/seo/target-card.blade.php'),
            resource_path('views/components/seo/filter-bar.blade.php'),
            resource_path('views/components/seo/status-badge.blade.php'),
            resource_path('views/components/seo/health-summary.blade.php'),
            resource_path('views/components/seo/empty-state.blade.php'),
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            $this->assertIsString($contents);
            $this->assertStringNotContainsString('Livewire', $contents, $file);
            $this->assertStringNotContainsString('livewire', $contents, $file);
            $this->assertStringNotContainsString('cdn.tailwindcss.com', $contents, $file);
            $this->assertStringNotContainsString('unpkg.com/alpine', $contents, $file);
            $this->assertStringNotContainsString('127.0.0.1', $contents, $file);
            $this->assertStringNotContainsString('page_translations', $contents, $file);
            $this->assertStringNotContainsString('post_translations', $contents, $file);
            $this->assertStringNotContainsString('section_translations', $contents, $file);
        }
    }

    private function locale(string $code): Locale
    {
        app(LocaleSettingsStore::class)->ensureSeeded();

        return Locale::query()->where('locale', $code)->firstOrFail();
    }

    /** @param array<string, mixed> $overrides */
    private function seoRecord(array $overrides = []): SeoRecord
    {
        return SeoRecord::query()->create([
            'locale_id' => $this->locale('en')->id,
            'target_type' => 'homepage',
            'target_key' => 'home',
            'meta_title' => 'Temp Mail SaaS',
            'meta_description' => 'Create temporary inboxes with language-specific SEO metadata.',
            'canonical_url' => url('/en'),
            'robots_index' => true,
            'robots_follow' => true,
            'include_in_sitemap' => true,
            'sitemap_priority' => 1.0,
            'sitemap_change_frequency' => 'daily',
            'twitter_card' => 'summary_large_image',
            ...$overrides,
        ]);
    }

    /** @return array<string, mixed> */
    private function validSeoPayload(): array
    {
        return [
            'meta_title' => 'Temp Mail SaaS metadata editor for private inbox growth',
            'meta_description' => 'Create temporary inboxes with language-specific SEO metadata, safe previews, and social card readiness for search growth.',
            'canonical_url' => url('/en'),
            'robots_index' => '1',
            'robots_follow' => '1',
            'include_in_sitemap' => '1',
            'sitemap_priority' => 1.0,
            'sitemap_change_frequency' => 'daily',
            'og_title' => 'Temp Mail SaaS',
            'og_description' => 'Social card readiness.',
            'og_image_media_id' => '',
            'twitter_card' => 'summary_large_image',
            'twitter_title' => 'Temp Mail SaaS',
            'twitter_description' => 'Twitter card readiness.',
            'twitter_image_media_id' => '',
            'schema_type' => 'WebSite',
            'schema_json_text' => '{"@context":"https://schema.org","@type":"WebSite","name":"Temp Mail SaaS"}',
            'breadcrumb_title' => 'Home',
        ];
    }

    private function mediaAsset(string $name): MediaAsset
    {
        return MediaAsset::query()->create([
            'uuid' => (string) Str::uuid(),
            'original_name' => $name,
            'file_name' => $name,
            'disk' => 'public',
            'path' => 'media/'.$name,
            'mime_type' => 'image/png',
            'size_bytes' => 1000,
            'width' => 1200,
            'height' => 630,
            'type' => 'seo',
            'status' => 'active',
            'title' => str($name)->before('.')->headline()->toString(),
            'uploaded_by' => User::factory()->admin()->create()->id,
        ]);
    }
}
