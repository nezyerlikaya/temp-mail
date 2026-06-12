<?php

namespace App\Services\Blog;

use App\Models\BlogTag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TagSearchService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = BlogTag::query()
            ->with('locale')
            ->withCount('posts')
            ->orderBy('name');

        if (($filters['q'] ?? '') !== '') {
            $needle = (string) $filters['q'];
            $query->where(fn ($query) => $query->where('name', 'like', '%'.$needle.'%')->orWhere('slug', 'like', '%'.$needle.'%'));
        }

        if (($filters['locale_id'] ?? 'all') !== 'all') {
            $query->where('locale_id', (int) $filters['locale_id']);
        }

        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('status', (string) $filters['status']);
        }

        return $query->paginate(12, ['*'], 'tags_page')->withQueryString();
    }
}
