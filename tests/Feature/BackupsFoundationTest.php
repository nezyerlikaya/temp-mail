<?php

namespace Tests\Feature;

use App\Models\SystemBackup;
use App\Models\User;
use App\Services\Backups\BackupPathResolver;
use App\Services\Backups\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class BackupsFoundationTest extends TestCase
{
    use RefreshDatabase;

    private string $recoveryPath;

    private ?string $originalRecovery = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->recoveryPath = storage_path('app/installer-recovery.flag');
        $this->originalRecovery = file_exists($this->recoveryPath) ? file_get_contents($this->recoveryPath) : null;

        if (file_exists($this->recoveryPath)) {
            unlink($this->recoveryPath);
        }

        File::deleteDirectory(storage_path('app/backups'));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/backups'));

        if ($this->originalRecovery !== null) {
            file_put_contents($this->recoveryPath, $this->originalRecovery);
        } elseif (file_exists($this->recoveryPath)) {
            unlink($this->recoveryPath);
        }

        parent::tearDown();
    }

    public function test_backup_page_renders_inside_admin_shell_and_replaces_placeholder(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('admin.backups-health.index'))
            ->assertOk()
            ->assertSee('Backups & Health')
            ->assertSee('Manual Backup')
            ->assertSee('Disk Space')
            ->assertSee('Operations workspace')
            ->assertDontSee('The route, authorization boundary');
    }

    public function test_admin_can_create_database_backup_outside_public_directory_without_env_secrets(): void
    {
        config(['app.key' => 'raw-secret-key-must-not-export!!']);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.backups-health.store'), ['type' => 'database'])
            ->assertRedirect(route('admin.backups-health.index'));

        $backup = SystemBackup::query()->firstOrFail();
        $path = app(BackupPathResolver::class)->absolutePath($backup);

        $this->assertSame('completed', $backup->status);
        $this->assertFileExists($path);
        $this->assertStringStartsNotWith(realpath(public_path()), realpath($path));
        $this->assertDatabaseHas('user_audit_events', ['event' => 'backup.created', 'actor_id' => $admin->id]);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path));
        $config = $zip->getFromName('config.json');
        $manifest = $zip->getFromName('manifest.json');
        $zip->close();

        $this->assertIsString($config);
        $this->assertIsString($manifest);
        $this->assertStringNotContainsString('raw-secret-key-must-not-export', $config.$manifest);
        $this->assertStringNotContainsString('APP_KEY', $config.$manifest);
    }

    public function test_owner_only_download_and_delete_backup(): void
    {
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $backup = app(BackupService::class)->create($owner, 'database');

        $this->actingAs($admin)
            ->get(route('admin.backups-health.download', $backup))
            ->assertForbidden();

        $this->actingAs($admin)
            ->delete(route('admin.backups-health.destroy', $backup))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('admin.backups-health.download', $backup))
            ->assertOk();

        $this->actingAs($owner)
            ->delete(route('admin.backups-health.destroy', $backup))
            ->assertRedirect(route('admin.backups-health.index'));

        $this->assertDatabaseMissing('system_backups', ['id' => $backup->id]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'backup.downloaded', 'actor_id' => $owner->id]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'backup.deleted', 'actor_id' => $owner->id]);
    }

    public function test_path_traversal_backup_paths_are_rejected(): void
    {
        $owner = User::factory()->owner()->create();
        $backup = SystemBackup::query()->create([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'type' => 'database',
            'status' => 'completed',
            'disk' => 'local',
            'path' => '../outside.zip',
            'filename' => 'outside.zip',
            'size_bytes' => 10,
            'checksum' => 'invalid',
            'created_by' => $owner->id,
            'completed_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('admin.backups-health.download', $backup))
            ->assertNotFound();

        $this->actingAs($owner)
            ->delete(route('admin.backups-health.destroy', $backup))
            ->assertNotFound();
    }

    public function test_backup_validation_errors_render_accessible_summary(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->followingRedirects()
            ->from(route('admin.backups-health.index'))
            ->post(route('admin.backups-health.store'), ['type' => 'restore'])
            ->assertOk()
            ->assertSee('Please fix the highlighted fields.')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('role="alert"', false);
    }
}
