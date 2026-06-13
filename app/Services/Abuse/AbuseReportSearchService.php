<?php

namespace App\Services\Abuse;

use App\Models\AbuseReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AbuseReportSearchService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        return AbuseReport::query()
            ->with(['assignee', 'domain', 'mailbox'])
            ->when(($filters['report_type'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('report_type', $filters['report_type']))
            ->when(($filters['status'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(($filters['priority'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('priority', $filters['priority']))
            ->when(filled($filters['assigned_to'] ?? null), fn (Builder $query) => $query->where('assigned_to', $filters['assigned_to']))
            ->when(filled($filters['date'] ?? null), fn (Builder $query) => $query->whereDate('created_at', $filters['date']))
            ->when(filled($filters['q'] ?? null), function (Builder $query) use ($filters): void {
                $needle = (string) $filters['q'];
                $query->where(fn (Builder $inner) => $inner
                    ->where('case_reference', 'like', '%'.$needle.'%')
                    ->orWhere('reporter_email', 'like', '%'.$needle.'%')
                    ->orWhere('subject', 'like', '%'.$needle.'%'));
            })
            ->orderByRaw("case priority when 'critical' then 1 when 'high' then 2 when 'normal' then 3 else 4 end")
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }
}
