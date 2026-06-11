<?php

namespace App\Services\Backups;

use App\Models\SystemBackup;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class BackupService
{
    public function __construct(
        private readonly DatabaseBackupAction $database,
        private readonly StorageBackupAction $storage,
        private readonly DiskSpaceChecker $diskSpace,
        private readonly BackupIntegrityChecker $integrity,
        private readonly BackupPathResolver $paths,
        private readonly AuditLogger $audit,
    ) {}

    public function directory(): string
    {
        return $this->paths->directory();
    }

    /** @return Collection<int, SystemBackup> */
    public function backups(): Collection
    {
        return SystemBackup::query()
            ->with('creator')
            ->latest()
            ->get();
    }

    /** @return array{total: int, completed: int, failed: int, total_size: int, recommended_keep: int, pre_update_ready: bool} */
    public function summary(): array
    {
        return [
            'total' => SystemBackup::query()->count(),
            'completed' => SystemBackup::query()->where('status', 'completed')->count(),
            'failed' => SystemBackup::query()->where('status', 'failed')->count(),
            'total_size' => (int) SystemBackup::query()->sum('size_bytes'),
            'recommended_keep' => 5,
            'pre_update_ready' => true,
        ];
    }

    public function create(User $actor, string $type): SystemBackup
    {
        $uuid = (string) Str::uuid();
        $filename = 'backup-'.$type.'-'.now()->format('Ymd-His').'-'.$uuid.'.zip';
        $path = $uuid.'/'.$filename;

        $backup = SystemBackup::query()->create([
            'uuid' => $uuid,
            'type' => $type,
            'status' => 'failed',
            'disk' => 'local',
            'path' => $path,
            'filename' => $filename,
            'created_by' => $actor->id,
            'manifest' => ['retention_recommendation' => 'Keep the latest 5 backups.'],
        ]);

        try {
            $this->diskSpace->assertEnough();
            $this->paths->assertPrivate();
            File::ensureDirectoryExists(dirname($this->absolutePath($backup)));

            $zip = new ZipArchive;
            if ($zip->open($this->absolutePath($backup), ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('The backup archive could not be opened for writing.');
            }

            $manifest = $this->baseManifest($backup, $actor);
            $this->addConfigSnapshot($zip);

            if (in_array($type, ['database', 'full'], true)) {
                $manifest['database'] = $this->database->writeTo($zip);
            }

            if (in_array($type, ['storage', 'full'], true)) {
                $manifest['storage'] = $this->storage->writeTo($zip);
            }

            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
            $zip->close();

            clearstatcache(true, $this->absolutePath($backup));

            $backup->forceFill([
                'status' => 'completed',
                'size_bytes' => filesize($this->absolutePath($backup)) ?: 0,
                'checksum' => hash_file('sha256', $this->absolutePath($backup)),
                'manifest' => $manifest,
                'failure_reason' => null,
                'completed_at' => now(),
            ])->save();

            $this->integrity->check($backup);

            $this->audit->record('backup.created', $actor, $actor, [
                'type' => $type,
                'backup_uuid' => $backup->uuid,
                'size_bytes' => $backup->size_bytes,
            ], ['module' => 'backup', 'action' => 'Backup created', 'severity' => 'critical']);
        } catch (\Throwable $exception) {
            $backup->forceFill([
                'status' => 'failed',
                'failure_reason' => $exception->getMessage(),
            ])->save();

            $this->audit->record('backup.failed', $actor, $actor, [
                'type' => $type,
                'backup_uuid' => $backup->uuid,
                'notification_ready' => true,
            ], ['module' => 'backup', 'action' => 'Backup failed', 'severity' => 'warning']);
        }

        return $backup->refresh();
    }

    public function absolutePath(SystemBackup $backup): string
    {
        return $this->paths->absolutePath($backup);
    }

    /** @return array<string, mixed> */
    private function baseManifest(SystemBackup $backup, User $actor): array
    {
        return [
            'uuid' => $backup->uuid,
            'type' => $backup->type,
            'created_at' => now()->toIso8601String(),
            'created_by' => $actor->email,
            'app' => [
                'name' => config('app.name'),
                'environment' => app()->environment(),
                'laravel' => app()->version(),
            ],
            'restore' => [
                'implemented' => false,
                'message' => 'Restore is intentionally out of MVP scope.',
            ],
        ];
    }

    private function addConfigSnapshot(ZipArchive $zip): void
    {
        $config = [
            'app' => [
                'name' => config('app.name'),
                'locale' => config('app.locale'),
                'fallback_locale' => config('app.fallback_locale'),
                'timezone' => config('app.timezone'),
            ],
            'database' => [
                'default' => config('database.default'),
            ],
            'filesystems' => [
                'default' => config('filesystems.default'),
            ],
        ];

        $zip->addFromString('config.json', json_encode($config, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }
}
