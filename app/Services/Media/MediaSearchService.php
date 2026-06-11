<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MediaSearchService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = MediaAsset::query()->with('uploader')->latest();

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

        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (($filters['uploader'] ?? '') !== '') {
            $query->where('uploaded_by', (int) $filters['uploader']);
        }

        if (($filters['date'] ?? 'all') === 'today') {
            $query->whereDate('created_at', today());
        }

        if (($filters['date'] ?? 'all') === 'week') {
            $query->where('created_at', '>=', now()->subWeek());
        }

        return $query->paginate(12)->withQueryString();
    }
}
