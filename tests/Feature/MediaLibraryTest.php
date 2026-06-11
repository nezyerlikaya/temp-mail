<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Media\MediaUrlResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_media_library_renders_inside_admin_shell(): void
    {
        $admin = User::factory()->admin()->create();
        $asset = $this->seedAsset($admin);

        $this->actingAs($admin)
            ->get(route('admin.media-library.index'))
            ->assertOk()
            ->assertSee('Media Library')
            ->assertSee('Upload media')
            ->assertSee('Media results')
            ->assertSee($asset->title)
            ->assertSee('Recent uploads');
    }

    public function test_media_upload_persists_asset_and_uses_safe_public_url(): void
    {
        $admin = User::factory()->admin()->create();
        $file = UploadedFile::fake()->create('hero-banner.pdf', 256, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('admin.media-library.store'), [
                'file' => $file,
                'title' => 'Homepage hero',
                'alt_text' => 'A calm dashboard cover',
                'caption' => 'Premium onboarding artwork.',
                'type' => 'document',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.media-library.index'));

        $asset = MediaAsset::query()->latest('id')->firstOrFail();
        Storage::disk('public')->assertExists($asset->path);

        $url = app(MediaUrlResolver::class)->url($asset);

        $this->assertStringStartsWith('/', $url);
        $this->assertStringNotContainsString('127.0.0.1', $url);
        $this->assertDatabaseHas('media_assets', [
            'uuid' => $asset->uuid,
            'title' => 'Homepage hero',
            'uploaded_by' => $admin->id,
        ]);
    }

    public function test_validation_errors_render_accessible_summary(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->followingRedirects()
            ->from(route('admin.media-library.index'))
            ->post(route('admin.media-library.store'), [])
            ->assertOk()
            ->assertSee('Please fix the highlighted fields.')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('role="alert"', false);
    }

    public function test_editor_can_view_but_cannot_upload_or_update_media(): void
    {
        $editor = User::factory()->editor()->create();
        $asset = $this->seedAsset(User::factory()->admin()->create());

        $this->actingAs($editor)
            ->get(route('admin.media-library.index'))
            ->assertOk()
            ->assertSee('Media Library');

        $this->actingAs($editor)
            ->post(route('admin.media-library.store'), [
                'file' => UploadedFile::fake()->create('blocked.pdf', 64, 'application/pdf'),
            ])
            ->assertForbidden();

        $this->actingAs($editor)
            ->put(route('admin.media-library.update', $asset), [
                'title' => 'Blocked',
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_edit_page_renders_safe_public_file_reference(): void
    {
        $admin = User::factory()->admin()->create();
        $asset = $this->seedAsset($admin);

        $this->actingAs($admin)
            ->get(route('admin.media-library.edit', $asset))
            ->assertOk()
            ->assertSee('Media asset detail')
            ->assertSee('Open public file')
            ->assertSee($asset->title)
            ->assertDontSee('127.0.0.1');
    }

    private function seedAsset(User $uploader): MediaAsset
    {
        $path = 'media/'.Str::uuid().'/hero.png';
        Storage::disk('public')->put($path, 'fake-image');

        return MediaAsset::query()->create([
            'uuid' => (string) Str::uuid(),
            'original_name' => 'hero.png',
            'file_name' => 'hero.png',
            'disk' => 'public',
            'path' => $path,
            'mime_type' => 'image/png',
            'size_bytes' => 1024,
            'width' => 1200,
            'height' => 800,
            'type' => 'image',
            'status' => 'active',
            'title' => 'Homepage hero',
            'alt_text' => 'A calm dashboard cover',
            'caption' => 'Premium onboarding artwork.',
            'uploaded_by' => $uploader->id,
        ]);
    }
}
