<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MediaLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_media_detail_renders_lifecycle_avatar_and_seo_readiness_warnings(): void
    {
        $admin = User::factory()->admin()->create();
        $asset = $this->seedAsset($admin, [
            'type' => 'seo',
            'alt_text' => null,
            'size_bytes' => 3 * 1024 * 1024,
            'width' => 900,
            'height' => 900,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.media-library.edit', $asset))
            ->assertOk()
            ->assertSee('Lifecycle controls')
            ->assertSee('Quality checks')
            ->assertSee('Missing alt text')
            ->assertSee('Avatar readiness')
            ->assertSee('SEO / OG readiness')
            ->assertSee('Recommended OG dimensions are 1200 x 630 pixels.')
            ->assertDontSee('storage/app')
            ->assertDontSee('C:\\');
    }

    public function test_admin_can_trash_and_restore_media_and_events_are_audited(): void
    {
        $admin = User::factory()->admin()->create();
        $asset = $this->seedAsset($admin);

        $this->actingAs($admin)
            ->post(route('admin.media-library.trash', $asset))
            ->assertRedirect();

        $this->assertDatabaseHas('media_assets', ['id' => $asset->id, 'status' => 'trashed']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'media.trashed', 'actor_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.media-library.restore', $asset))
            ->assertRedirect();

        $this->assertDatabaseHas('media_assets', ['id' => $asset->id, 'status' => 'active']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'media.restored', 'actor_id' => $admin->id]);
    }

    public function test_editor_cannot_permanently_delete_media(): void
    {
        $editor = User::factory()->editor()->create();
        $asset = $this->seedAsset(User::factory()->admin()->create(), ['status' => 'trashed']);

        $this->actingAs($editor)
            ->delete(route('admin.media-library.destroy', $asset), [
                'delete_confirmation' => 'DELETE',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('media_assets', ['id' => $asset->id]);
    }

    public function test_permanent_delete_requires_trash_first_and_removes_file_when_allowed(): void
    {
        $owner = User::factory()->owner()->create();
        $asset = $this->seedAsset($owner);

        $this->actingAs($owner)
            ->from(route('admin.media-library.edit', $asset))
            ->delete(route('admin.media-library.destroy', $asset), [
                'delete_confirmation' => 'DELETE',
            ])
            ->assertSessionHasErrors('delete_confirmation');

        $asset->update(['status' => 'trashed']);

        $this->actingAs($owner)
            ->delete(route('admin.media-library.destroy', $asset), [
                'delete_confirmation' => 'DELETE',
            ])
            ->assertRedirect(route('admin.media-library.index', ['status' => 'trashed']));

        $this->assertDatabaseMissing('media_assets', ['id' => $asset->id]);
        Storage::disk('public')->assertMissing($asset->path);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'media.deleted', 'actor_id' => $owner->id]);
    }

    public function test_in_use_media_delete_requires_explicit_confirmation(): void
    {
        $owner = User::factory()->owner()->create();
        $asset = $this->seedAsset($owner, ['status' => 'trashed']);

        MediaUsage::query()->create([
            'media_asset_id' => $asset->id,
            'module' => 'seo',
            'usage_context' => 'open_graph',
            'slot' => 'og_image',
            'usable_type' => 'seo_records',
            'usable_id' => 'homepage',
            'label' => 'Homepage OG image',
            'metadata' => [],
            'attached_by' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->from(route('admin.media-library.edit', $asset))
            ->delete(route('admin.media-library.destroy', $asset), [
                'delete_confirmation' => 'DELETE',
            ])
            ->assertSessionHasErrors('confirm_in_use_delete');

        $this->assertDatabaseHas('media_assets', ['id' => $asset->id]);

        $this->actingAs($owner)
            ->delete(route('admin.media-library.destroy', $asset), [
                'delete_confirmation' => 'DELETE',
                'confirm_in_use_delete' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('media_assets', ['id' => $asset->id]);
        $this->assertDatabaseMissing('media_usages', ['media_asset_id' => $asset->id]);
    }

    public function test_orphaned_filter_and_hidden_status_render_in_media_library(): void
    {
        $admin = User::factory()->admin()->create();
        $asset = $this->seedAsset($admin, ['status' => 'hidden', 'title' => 'Hidden Orphan']);
        $used = $this->seedAsset($admin, ['title' => 'Used Asset']);
        MediaUsage::query()->create([
            'media_asset_id' => $used->id,
            'module' => 'avatars',
            'usage_context' => 'user_profile',
            'slot' => 'avatar_media_id',
            'usable_type' => User::class,
            'usable_id' => (string) $admin->id,
            'label' => 'Admin avatar',
            'metadata' => [],
            'attached_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.media-library.index', ['usage' => 'orphaned', 'status' => 'hidden']))
            ->assertOk()
            ->assertSee('Hidden Orphan')
            ->assertSee('Hidden')
            ->assertSee('Orphaned');

        $this->assertSame('hidden', $asset->refresh()->status);
    }

    private function seedAsset(User $uploader, array $overrides = []): MediaAsset
    {
        $path = 'media/'.Str::uuid().'/asset.png';
        Storage::disk('public')->put($path, 'fake-image');

        return MediaAsset::query()->create([
            'uuid' => (string) Str::uuid(),
            'original_name' => 'asset.png',
            'file_name' => 'asset.png',
            'disk' => 'public',
            'path' => $path,
            'mime_type' => 'image/png',
            'size_bytes' => 1024,
            'width' => 1200,
            'height' => 630,
            'type' => 'image',
            'status' => 'active',
            'title' => 'Lifecycle Asset',
            'alt_text' => 'Lifecycle image',
            'caption' => 'Lifecycle ready.',
            'uploaded_by' => $uploader->id,
            ...$overrides,
        ]);
    }
}
