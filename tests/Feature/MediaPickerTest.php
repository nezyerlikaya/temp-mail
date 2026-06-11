<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Media\MediaUsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MediaPickerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_media_picker_renders_in_settings_and_profile_views_without_raw_path_inputs(): void
    {
        $owner = User::factory()->owner()->create();
        $asset = $this->seedAsset($owner);

        $this->actingAs($owner)
            ->get(route('admin.settings.index', ['group' => 'brand']))
            ->assertOk()
            ->assertSee('Choose media')
            ->assertSee('No media selected')
            ->assertDontSee('Media reference #')
            ->assertDontSee('file path', false);

        $this->actingAs($owner)
            ->get(route('admin.people-identity.show', $owner))
            ->assertOk()
            ->assertSee('Avatar media')
            ->assertSee('Choose media')
            ->assertDontSee('Open Media Library hook')
            ->assertDontSee('file path', false);
    }

    public function test_media_picker_search_returns_safe_json_results(): void
    {
        $owner = User::factory()->owner()->create();
        $asset = $this->seedAsset($owner);

        $response = $this->actingAs($owner)
            ->getJson(route('admin.media-library.picker', ['q' => 'picker', 'type' => 'image']));

        $response->assertOk()
            ->assertJsonPath('assets.0.id', $asset->id)
            ->assertJsonPath('assets.0.title', 'Picker Hero')
            ->assertJsonPath('assets.0.usage_count', 0)
            ->assertJsonStructure(['assets' => [['id', 'title', 'original_name', 'type', 'mime_type', 'url', 'usage_count']]]);

        $this->assertStringNotContainsString('127.0.0.1', (string) $response->getContent());
    }

    public function test_brand_settings_attach_and_detach_media_usage(): void
    {
        $owner = User::factory()->owner()->create();
        $asset = $this->seedAsset($owner);

        $this->actingAs($owner)
            ->put(route('admin.settings.brand.update'), [
                'logo_media_id' => $asset->id,
                'favicon_media_id' => null,
                'app_icon_media_id' => null,
                'public_site_name' => 'Temp Mail Cloud',
                'footer_brand_text' => 'Temp Mail Cloud',
            ])
            ->assertRedirect(route('admin.settings.index', ['group' => 'brand']));

        $this->assertDatabaseHas('media_usages', [
            'media_asset_id' => $asset->id,
            'module' => 'settings',
            'usage_context' => 'brand',
            'slot' => 'logo_media_id',
            'usable_type' => 'system_settings',
            'usable_id' => 'brand',
        ]);

        $this->actingAs($owner)
            ->put(route('admin.settings.brand.update'), [
                'logo_media_id' => null,
                'favicon_media_id' => null,
                'app_icon_media_id' => null,
                'public_site_name' => 'Temp Mail Cloud',
                'footer_brand_text' => 'Temp Mail Cloud',
            ])
            ->assertRedirect(route('admin.settings.index', ['group' => 'brand']));

        $this->assertDatabaseMissing('media_usages', [
            'media_asset_id' => $asset->id,
            'module' => 'settings',
            'usage_context' => 'brand',
            'slot' => 'logo_media_id',
            'usable_type' => 'system_settings',
            'usable_id' => 'brand',
        ]);
    }

    public function test_avatar_updates_attach_media_usage_and_detail_page_shows_used_by_panel(): void
    {
        $owner = User::factory()->owner()->create();
        $asset = $this->seedAsset($owner);

        $this->actingAs($owner)
            ->patch(route('admin.people-identity.avatar.update', $owner), [
                'avatar_media_id' => $asset->id,
                'avatar_color' => '#0f766e',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('media_usages', [
            'media_asset_id' => $asset->id,
            'module' => 'avatars',
            'usage_context' => 'user_profile',
            'slot' => 'avatar_media_id',
            'usable_type' => User::class,
            'usable_id' => (string) $owner->id,
        ]);

        $this->actingAs($owner)
            ->get(route('admin.media-library.edit', $asset))
            ->assertOk()
            ->assertSee('Used by')
            ->assertSee('Avatars readiness')
            ->assertSee('1 uses');
    }

    public function test_media_usage_service_marks_orphaned_assets_safely(): void
    {
        $owner = User::factory()->owner()->create();
        $asset = $this->seedAsset($owner);

        $summary = app(MediaUsageService::class)->summary($asset);

        $this->assertTrue($summary['orphaned']);
        $this->assertSame(0, $summary['total']);
        $this->assertSame('ready', $summary['readiness'][0]['status']);
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
