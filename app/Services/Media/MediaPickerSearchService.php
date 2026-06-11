<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Collection;

class MediaPickerSearchService
{
    public function __construct(private readonly MediaUrlResolver $urls) {}

    /** @param array<string, mixed> $filters */
    public function search(array $filters, int $limit = 24): Collection
    {
        $query = MediaAsset::query()
            ->withCount('usages')
            ->where('status', 'active')
            ->latest();

        if (($filters['q'] ?? '') !== '') {
            $needle = (string) $filters['q'];
            $query->where(function ($query) use ($needle): void {
                $query->where('original_name', 'like', '%'.$needle.'%')
                    ->orWhere('file_name', 'like', '%'.$needle.'%')
                    ->orWhere('title', 'like', '%'.$needle.'%')
                    ->orWhere('alt_text', 'like', '%'.$needle.'%');
            });
        }

        if (($filters['type'] ?? 'all') !== 'all') {
            $query->where('type', $filters['type']);
        }

        return $query->limit($limit)->get();
    }

    /** @param array<string, mixed> $filters */
    public function options(array $filters = [], int $limit = 24): array
    {
        return $this->search($filters, $limit)
            ->map(fn (MediaAsset $asset): array => $this->option($asset))
            ->values()
            ->all();
    }

    public function option(?MediaAsset $asset): ?array
    {
        if (! $asset) {
            return null;
        }

        $asset->loadCount('usages');

        return [
            'id' => $asset->id,
            'title' => $asset->title ?: $asset->original_name,
            'original_name' => $asset->original_name,
            'type' => $asset->type,
            'mime_type' => $asset->mime_type,
            'url' => $this->urls->url($asset),
            'usage_count' => (int) ($asset->usages_count ?? 0),
        ];
    }
}
