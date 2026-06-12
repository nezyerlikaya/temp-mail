<?php

namespace App\Services\Domains;

use App\Models\Domain;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class DomainSearchService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        return Domain::query()
            ->when(filled($filters['q'] ?? null), function (Builder $query) use ($filters): void {
                $term = '%'.strtolower((string) $filters['q']).'%';
                $query->where(function (Builder $inner) use ($term): void {
                    $inner->whereRaw('LOWER(domain_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(display_name) LIKE ?', [$term]);
                });
            })
            ->when(($filters['status'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(($filters['active'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('is_active', ($filters['active'] ?? null) === 'active'))
            ->when(($filters['visibility'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('is_public', ($filters['visibility'] ?? null) === 'public'))
            ->when(($filters['dns'] ?? 'all') !== 'all', function (Builder $query) use ($filters): void {
                $filters['dns'] === 'ready'
                    ? $query->whereIn('status', ['ready'])
                    : $query->whereIn('status', ['draft', 'pending_dns', 'degraded']);
            })
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->orderBy('domain_name')
            ->paginate((int) ($filters['per_page'] ?? 12))
            ->withQueryString();
    }
}
