<?php

namespace App\Services\Mail;

use App\Models\SmtpConnection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SmtpConnectionService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        return SmtpConnection::query()
            ->with('domain')
            ->when(filled($filters['smtp_q'] ?? null), function (Builder $query) use ($filters): void {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], (string) $filters['smtp_q']).'%';
                $query->where(fn (Builder $nested) => $nested
                    ->where('name', 'like', $term)
                    ->orWhere('host', 'like', $term)
                    ->orWhere('from_email', 'like', $term)
                    ->orWhereHas('domain', fn (Builder $domain) => $domain->where('domain_name', 'like', $term)));
            })
            ->when(($filters['smtp_status'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('status', $filters['smtp_status']))
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(12, ['*'], 'smtp_page')
            ->withQueryString();
    }

    /** @return array{total: int, active: int, connected: int, failed: int, default: string|null} */
    public function summary(): array
    {
        return [
            'total' => SmtpConnection::query()->count(),
            'active' => SmtpConnection::query()->where('is_active', true)->count(),
            'connected' => SmtpConnection::query()->where('status', 'connected')->count(),
            'failed' => SmtpConnection::query()->where('status', 'failed')->count(),
            'default' => SmtpConnection::query()->where('is_default', true)->value('name'),
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
