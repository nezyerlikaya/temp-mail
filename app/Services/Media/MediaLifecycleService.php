<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use Illuminate\Validation\ValidationException;

class MediaLifecycleService
{
    /** @return array<int, string> */
    public function statuses(): array
    {
        return ['active', 'hidden', 'trashed'];
    }

    public function trash(MediaAsset $asset): void
    {
        if ($asset->status === 'trashed') {
            throw ValidationException::withMessages([
                'status' => 'This media asset is already in trash.',
            ]);
        }

        $asset->update(['status' => 'trashed']);
    }

    public function restore(MediaAsset $asset): void
    {
        if ($asset->status !== 'trashed') {
            throw ValidationException::withMessages([
                'status' => 'Only trashed media assets can be restored.',
            ]);
        }

        $asset->update(['status' => 'active']);
    }

    public function updateStatus(MediaAsset $asset, string $status): void
    {
        if (! in_array($status, ['active', 'hidden'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Choose active or hidden. Use the trash action to move media to trash.',
            ]);
        }

        if ($asset->status === 'trashed') {
            throw ValidationException::withMessages([
                'status' => 'Restore this media asset before changing its visibility.',
            ]);
        }

        $asset->update(['status' => $status]);
    }

    /** @return array{is_trashed: bool, in_use: bool, usage_count: int, can_permanently_delete: bool} */
    public function state(MediaAsset $asset): array
    {
        $usageCount = $asset->usages()->count();

        return [
            'is_trashed' => $asset->status === 'trashed',
            'in_use' => $usageCount > 0,
            'usage_count' => $usageCount,
            'can_permanently_delete' => $asset->status === 'trashed',
        ];
    }
}
