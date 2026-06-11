<?php

namespace App\Services\Pages;

use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PageSearchService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = Page::query()
            ->with(['locale', 'author'])
            ->latest();

        if (($filters['q'] ?? '') !== '') {
            $needle = (string) $filters['q'];
            $query->where(function ($query) use ($needle): void {
                $query->where('title', 'like', '%'.$needle.'%')
                    ->orWhere('slug', 'like', '%'.$needle.'%');
            });
        }

        if (($filters['locale_id'] ?? 'all') !== 'all') {
            $query->where('locale_id', (int) $filters['locale_id']);
        }

        if (($filters['page_type'] ?? 'all') !== 'all') {
            $query->where('page_type', $filters['page_type']);
        }

        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (($filters['author_id'] ?? 'all') !== 'all') {
            $query->where('author_id', (int) $filters['author_id']);
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
