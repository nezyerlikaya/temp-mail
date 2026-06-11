<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Collection;

class MediaAssetService
{
    /** @return array{total: int, images: int, documents: int, active: int, hidden: int, trashed: int, orphaned: int} */
    public function summary(): array
    {
        return [
            'total' => MediaAsset::query()->count(),
            'images' => MediaAsset::query()->where('type', 'image')->count(),
            'documents' => MediaAsset::query()->where('type', 'document')->count(),
            'active' => MediaAsset::query()->where('status', 'active')->count(),
            'hidden' => MediaAsset::query()->where('status', 'hidden')->count(),
            'trashed' => MediaAsset::query()->where('status', 'trashed')->count(),
            'orphaned' => MediaAsset::query()->whereDoesntHave('usages')->count(),
        ];
    }

    /** @return Collection<int, MediaAsset> */
    public function recent(int $limit = 6): Collection
    {
        return MediaAsset::query()->with('uploader')->withCount('usages')->latest()->limit($limit)->get();
    }
}
