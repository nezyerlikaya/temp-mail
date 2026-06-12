<?php

namespace App\Services\Blog;

use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BlogPostSearchService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = BlogPost::query()
            ->with(['locale', 'author', 'category', 'tags'])
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

        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('status', (string) $filters['status']);
        } else {
            $query->where('status', '!=', 'trashed');
        }

        if (($filters['category_id'] ?? 'all') !== 'all') {
            $query->where('blog_category_id', (int) $filters['category_id']);
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
