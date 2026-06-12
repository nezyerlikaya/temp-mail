<?php

namespace App\Services\Sections;

use App\Models\Section;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SectionSearchService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = Section::query()
            ->with(['locale', 'creator', 'updater'])
            ->withCount('items')
            ->orderBy('sort_order')
            ->latest();

        if (($filters['q'] ?? '') !== '') {
            $query->where('title', 'like', '%'.((string) $filters['q']).'%');
        }

        if (($filters['locale_id'] ?? 'all') !== 'all') {
            $query->where('locale_id', (int) $filters['locale_id']);
        }

        if (($filters['section_type'] ?? 'all') !== 'all') {
            $query->where('section_type', (string) $filters['section_type']);
        }

        if (($filters['placement'] ?? 'all') !== 'all') {
            $query->where('placement', (string) $filters['placement']);
        }

        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('status', (string) $filters['status']);
        } else {
            $query->where('status', '!=', 'trashed');
        }

        return $query->paginate(12)->withQueryString();
    }
}
