<?php

namespace App\Services\Translations;

use App\Models\TranslationSource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TranslationSearchService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        return TranslationSource::query()
            ->when(($filters['group'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('group_key', $filters['group']))
            ->when(($filters['requirement'] ?? 'all') === 'required', fn (Builder $query) => $query->where('is_required', true))
            ->when(($filters['requirement'] ?? 'all') === 'optional', fn (Builder $query) => $query->where('is_required', false))
            ->when(($filters['state'] ?? 'all') === 'active', fn (Builder $query) => $query->where('is_active', true))
            ->when(($filters['state'] ?? 'all') === 'passive', fn (Builder $query) => $query->where('is_active', false))
            ->when(($filters['missing'] ?? 'all') === 'missing', fn (Builder $query) => $query->whereDoesntHave('values', fn (Builder $valueQuery) => $valueQuery->where('status', 'translated')))
            ->when(filled($filters['q'] ?? null), function (Builder $query) use ($filters): void {
                $search = (string) $filters['q'];
                $query->where(fn (Builder $inner) => $inner
                    ->where('translation_key', 'like', '%'.$search.'%')
                    ->orWhere('source_value', 'like', '%'.$search.'%'));
            })
            ->orderBy('group_key')
            ->orderBy('sort_order')
            ->orderBy('translation_key')
            ->paginate((int) ($filters['per_page'] ?? 12))
            ->withQueryString();
    }
}
