<?php

namespace App\Actions\Media;

use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Media\MediaLifecycleService;

class TrashMediaAction
{
    public function __construct(
        private readonly MediaLifecycleService $lifecycle,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, MediaAsset $asset): void
    {
        $previousStatus = $asset->status;
        $this->lifecycle->trash($asset);

        $this->audit->record('media.trashed', $actor, null, [
            'media_uuid' => $asset->uuid,
            'previous_status' => $previousStatus,
        ], [
            'module' => 'media',
            'action' => 'Trash media',
            'severity' => 'warning',
            'target' => $asset,
        ]);
    }
}
