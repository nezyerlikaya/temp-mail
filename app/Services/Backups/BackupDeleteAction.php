<?php

namespace App\Services\Backups;

use App\Models\SystemBackup;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\File;

class BackupDeleteAction
{
    public function __construct(
        private readonly BackupPathResolver $paths,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(SystemBackup $backup, User $actor): void
    {
        try {
            $path = $this->paths->absolutePath($backup);
        } catch (\RuntimeException) {
            abort(404);
        }

        if (is_file($path)) {
            File::delete($path);
        }

        $uuid = $backup->uuid;
        $backup->delete();

        $this->audit->record('backup.deleted', $actor, $actor, [
            'backup_uuid' => $uuid,
        ], ['module' => 'backup', 'action' => 'Backup deleted', 'severity' => 'critical']);
    }
}
