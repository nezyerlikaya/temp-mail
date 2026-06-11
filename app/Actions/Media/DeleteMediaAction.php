<?php

namespace App\Actions\Media;

use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Media\MediaLifecycleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteMediaAction
{
    public function __construct(
        private readonly MediaLifecycleService $lifecycle,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, MediaAsset $asset, bool $confirmInUse): void
    {
        $state = $this->lifecycle->state($asset);

        if (! $state['is_trashed']) {
            throw ValidationException::withMessages([
                'delete_confirmation' => 'Move this media asset to trash before deleting it permanently.',
            ]);
        }

        if ($state['in_use'] && ! $confirmInUse) {
            throw ValidationException::withMessages([
                'confirm_in_use_delete' => 'This asset is still in use. Confirm that you understand its references will be removed.',
            ]);
        }

        $auditMetadata = [
            'media_uuid' => $asset->uuid,
            'original_name' => $asset->original_name,
            'usage_count' => $state['usage_count'],
        ];

        DB::transaction(function () use ($asset): void {
            $asset->usages()->delete();
            $asset->delete();
        });

        Storage::disk($asset->disk)->delete($asset->path);

        $this->audit->record('media.deleted', $actor, null, $auditMetadata, [
            'module' => 'media',
            'action' => 'Permanently delete media',
            'severity' => 'critical',
            'target_type' => MediaAsset::class,
            'target_id' => $asset->getKey(),
        ]);
    }
}
