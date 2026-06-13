<?php

namespace App\Services\Abuse;

use App\Models\AbuseCaseEvent;
use App\Models\AbuseReport;
use App\Models\User;
use App\Services\Audit\AuditCorrelationResolver;
use Illuminate\Database\Eloquent\Collection;

class AbuseCaseTimelineService
{
    public function __construct(private readonly AuditCorrelationResolver $correlation) {}

    /** @param array<string, scalar|null> $metadata */
    public function record(AbuseReport $report, ?User $actor, string $type, string $summary, array $metadata = [], ?string $correlationId = null): AbuseCaseEvent
    {
        return $report->events()->create([
            'actor_id' => $actor?->id,
            'event_type' => $type,
            'summary' => $summary,
            'correlation_id' => $correlationId ?? $this->correlation->resolve(),
            'metadata' => $metadata,
        ]);
    }

    /** @return Collection<int, AbuseCaseEvent> */
    public function forCase(AbuseReport $report): Collection
    {
        return $report->events()->with('actor')->limit(100)->get();
    }
}
