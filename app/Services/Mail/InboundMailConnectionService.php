<?php

namespace App\Services\Mail;

use App\Models\InboundMailConnection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class InboundMailConnectionService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        return InboundMailConnection::query()
            ->with('domain')
            ->when(filled($filters['q'] ?? null), function (Builder $query) use ($filters): void {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], (string) $filters['q']).'%';
                $query->where(fn (Builder $nested) => $nested
                    ->where('name', 'like', $term)
                    ->orWhere('host', 'like', $term)
                    ->orWhere('username', 'like', $term)
                    ->orWhereHas('domain', fn (Builder $domain) => $domain->where('domain_name', 'like', $term)));
            })
            ->when(($filters['status'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(($filters['domain_id'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('domain_id', $filters['domain_id']))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();
    }

    /** @return array{total: int, active: int, connected: int, failed: int, untested: int} */
    public function summary(): array
    {
        return [
            'total' => InboundMailConnection::query()->count(),
            'active' => InboundMailConnection::query()->where('is_active', true)->count(),
            'connected' => InboundMailConnection::query()->where('status', 'connected')->count(),
            'failed' => InboundMailConnection::query()->where('status', 'failed')->count(),
            'untested' => InboundMailConnection::query()->where('status', 'not_tested')->count(),
        ];
    }

    /** @return array<string, string> */
    public function statuses(): array
    {
        return [
            'not_tested' => 'Not tested',
            'connected' => 'Connected',
            'failed' => 'Failed',
            'disabled' => 'Disabled',
        ];
    }

    /** @return array<string, string> */
    public function encryptionOptions(): array
    {
        return [
            'none' => 'None',
            'ssl' => 'SSL',
            'tls' => 'TLS',
        ];
    }
}
