<?php

namespace App\Actions\Media;

use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Media\MediaLifecycleService;

class RestoreMediaAction
{
    public function __construct(
        private readonly MediaLifecycleService $lifecycle,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, MediaAsset $asset): void
    {
        $this->lifecycle->restore($asset);

        $this->audit->record('media.restored', $actor, null, [
            'media_uuid' => $asset->uuid,
        ], [
            'module' => 'media',
            'action' => 'Restore media',
            'target' => $asset,
        ]);
    }
}
