<?php

namespace App\Actions\Media;

use App\Models\MediaUsage;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class DetachMediaUsageAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $usage */
    public function handle(User $actor, array $usage): int
    {
        $query = MediaUsage::query()
            ->where('module', $usage['module'])
            ->where('usage_context', $usage['usage_context'])
            ->where('slot', $usage['slot'])
            ->where('usable_type', $usage['usable_type'] ?? null)
            ->where('usable_id', $usage['usable_id'] ?? null);

        if (isset($usage['media_asset_id'])) {
            $query->where('media_asset_id', $usage['media_asset_id']);
        }

        $deleted = $query->delete();

        if ($deleted > 0) {
            $this->audit->record('media.usage_detached', $actor, null, [
                'module' => $usage['module'],
                'usage_context' => $usage['usage_context'],
                'slot' => $usage['slot'],
                'removed' => $deleted,
            ], ['module' => 'media', 'action' => 'Detach media usage', 'severity' => 'info']);
        }

        return $deleted;
    }
}
