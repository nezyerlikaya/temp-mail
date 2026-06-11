<?php

namespace App\Services\Updates;

use App\Models\UpdateCheck;

class UpdateRollbackService
{
    /** @return array{ready: bool, status: string, message: string, last_installed_version: string|null} */
    public function readiness(?UpdateCheck $latestCheck = null): array
    {
        return [
            'ready' => false,
            'status' => 'warning',
            'message' => 'Rollback readiness is recorded, but full automatic restore remains outside this step.',
            'last_installed_version' => $latestCheck?->status === 'installed' ? $latestCheck->latest_version : null,
        ];
    }
}
