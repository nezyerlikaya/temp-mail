<?php

namespace App\Services\Audit;

use App\Models\UserAuditEvent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AuditSearchService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        return $this->query($filters)
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();
    }

    /** @param array<string, mixed> $filters */
    public function summary(array $filters): array
    {
        $base = $this->query($filters);

        return [
            'total' => (clone $base)->count(),
            'critical' => (clone $base)->where('severity', 'critical')->count(),
            'warnings' => (clone $base)->where('severity', 'warning')->count(),
            'actors' => (clone $base)->whereNotNull('actor_id')->distinct('actor_id')->count('actor_id'),
        ];
    }

    /** @return array{modules: Collection<int, string>, actions: Collection<int, string>, targetTypes: Collection<int, string>, severities: array<int, string>} */
    public function options(): array
    {
        return [
            'modules' => UserAuditEvent::query()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module'),
            'actions' => UserAuditEvent::query()->whereNotNull('action')->distinct()->orderBy('action')->pluck('action'),
            'targetTypes' => UserAuditEvent::query()->whereNotNull('target_type')->distinct()->orderBy('target_type')->pluck('target_type'),
            'severities' => ['info', 'warning', 'critical'],
        ];
    }

    /** @param array<string, mixed> $filters */
    public function query(array $filters): Builder
    {
        return UserAuditEvent::query()
            ->with(['actor', 'subject'])
            ->when($filters['module'] ?? null, fn (Builder $query, string $module): Builder => $query->where('module', $module))
            ->when($filters['action'] ?? null, fn (Builder $query, string $action): Builder => $query->where('action', $action))
            ->when($filters['severity'] ?? null, fn (Builder $query, string $severity): Builder => $query->where('severity', $severity))
            ->when($filters['target_type'] ?? null, fn (Builder $query, string $targetType): Builder => $query->where('target_type', $targetType))
            ->when($filters['correlation_id'] ?? null, fn (Builder $query, string $correlationId): Builder => $query->where('correlation_id', 'like', "%{$correlationId}%"))
            ->when($filters['actor'] ?? null, function (Builder $query, string $actor): Builder {
                return $query->whereHas('actor', fn (Builder $user): Builder => $user
                    ->where('name', 'like', "%{$actor}%")
                    ->orWhere('email', 'like', "%{$actor}%"));
            })
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date));
    }
}
