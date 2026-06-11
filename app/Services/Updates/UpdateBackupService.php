<?php

namespace App\Services\Updates;

use App\Models\SystemBackup;
use App\Models\User;
use App\Services\Backups\BackupService;

class UpdateBackupService
{
    public function __construct(private readonly BackupService $backups) {}

    /** @return array{required: bool, ready: bool, message: string, summary: array<string, mixed>} */
    public function readiness(): array
    {
        $summary = $this->backups->summary();

        return [
            'required' => true,
            'ready' => ($summary['completed'] ?? 0) > 0,
            'message' => 'Create or confirm a recent backup before installing updates.',
            'summary' => $summary,
        ];
    }

    public function createPreUpdateBackup(User $actor): SystemBackup
    {
        return $this->backups->create($actor, 'database');
    }
}
