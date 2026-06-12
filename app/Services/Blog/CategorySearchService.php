<?php

namespace App\Services\Blog;

use App\Models\BlogCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategorySearchService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = BlogCategory::query()
            ->with('locale')
            ->withCount('posts')
            ->orderBy('sort_order')
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

        return $query->paginate(12, ['*'], 'categories_page')->withQueryString();
    }
}
