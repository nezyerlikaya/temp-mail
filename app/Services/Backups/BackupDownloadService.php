<?php

namespace App\Services\Backups;

use App\Models\SystemBackup;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupDownloadService
{
    public function __construct(
        private readonly BackupPathResolver $paths,
        private readonly BackupIntegrityChecker $integrity,
        private readonly AuditLogger $audit,
    ) {}

    public function download(SystemBackup $backup, User $actor): BinaryFileResponse
    {
        try {
            $result = $this->integrity->check($backup);
        } catch (\RuntimeException) {
            abort(404);
        }

        if ($result['status'] !== 'passed') {
            abort(404);
        }

        $this->audit->record('backup.downloaded', $actor, $actor, [
            'backup_uuid' => $backup->uuid,
        ], ['module' => 'backup', 'action' => 'Backup downloaded', 'severity' => 'critical']);

        try {
            return response()->download($this->paths->absolutePath($backup), $backup->filename);
        } catch (\RuntimeException) {
            abort(404);
        }
    }
}
