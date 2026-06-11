<?php

namespace App\Services\Localization;

use App\Models\Locale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LocaleSearchService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = Locale::query()->orderBy('sort_order')->orderBy('language_name');

        if (($filters['q'] ?? '') !== '') {
            $needle = (string) $filters['q'];
            $query->where(function ($query) use ($needle): void {
                $query->where('language_name', 'like', '%'.$needle.'%')
                    ->orWhere('native_name', 'like', '%'.$needle.'%')
                    ->orWhere('locale', 'like', '%'.$needle.'%')
                    ->orWhere('region', 'like', '%'.$needle.'%');
            });
        }

        if (($filters['state'] ?? 'all') !== 'all') {
            $query->where('is_active', $filters['state'] === 'active');
        }

        if (($filters['direction'] ?? 'all') !== 'all') {
            $query->where('direction', $filters['direction']);
        }

        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('launch_status', $filters['status']);
        }

        if (($filters['readiness'] ?? 'all') !== 'all') {
            match ($filters['readiness']) {
                'high' => $query->where('market_readiness', 'ready')->whereIn('launch_status', ['ready', 'launched']),
                'needs_review' => $query->where('market_readiness', 'ready')->where('launch_status', 'draft'),
                'blocked' => $query->where('market_readiness', 'blocked'),
                default => null,
            };
        }

        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = in_array($perPage, [10, 20, 30], true) ? $perPage : 10;

        return $query->paginate($perPage)->withQueryString();
    }
}
