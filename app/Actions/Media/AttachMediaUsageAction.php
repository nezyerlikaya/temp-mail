<?php

namespace App\Actions\Media;

use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class AttachMediaUsageAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $usage */
    public function handle(User $actor, MediaAsset $asset, array $usage): MediaUsage
    {
        $record = MediaUsage::query()->updateOrCreate(
            [
                'media_asset_id' => $asset->id,
                'module' => $usage['module'],
                'usage_context' => $usage['usage_context'],
                'slot' => $usage['slot'],
                'usable_type' => $usage['usable_type'] ?? null,
                'usable_id' => $usage['usable_id'] ?? null,
            ],
            [
                'label' => $usage['label'] ?? null,
                'metadata' => $usage['metadata'] ?? null,
                'attached_by' => $actor->id,
            ],
        );

        $this->audit->record('media.usage_attached', $actor, null, [
            'media_uuid' => $asset->uuid,
            'module' => $record->module,
            'usage_context' => $record->usage_context,
            'slot' => $record->slot,
        ], ['module' => 'media', 'action' => 'Attach media usage', 'severity' => 'info']);

        return $record;
    }
}
