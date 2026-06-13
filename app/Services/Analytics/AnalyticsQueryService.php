<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsDailyMetric;
use App\Models\Domain;
use App\Models\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AnalyticsQueryService
{
    /** @param array{from: Carbon, to: Carbon} $range @param array<string, mixed> $filters */
    public function total(string $eventKey, array $range, array $filters = []): int
    {
        return (int) $this->base($range, $filters)
            ->where('event_key', $eventKey)
            ->sum('total_count');
    }

    /** @param array{from: Carbon, to: Carbon} $range @param array<int, string> $eventKeys @param array<string, mixed> $filters @return array<int, array{date: string, value: int}> */
    public function trend(array $eventKeys, array $range, array $filters = []): array
    {
        $values = $this->base($range, $filters)
            ->whereIn('event_key', $eventKeys)
            ->selectRaw('metric_date, SUM(total_count) as value')
            ->groupBy('metric_date')
            ->orderBy('metric_date')
            ->pluck('value', 'metric_date')
            ->all();

        return $this->dateBuckets($range['from'], $range['to'])
            ->map(fn (string $date): array => ['date' => $date, 'value' => (int) ($values[$date] ?? 0)])
            ->all();
    }

    /** @param array{from: Carbon, to: Carbon} $range @param array<string, mixed> $filters @return array<int, array{label: string, value: int}> */
    public function topDomains(array $range, array $filters = [], string $eventKey = 'mailbox.created', int $limit = 5): array
    {
        return $this->base($range, $filters)
            ->where('event_key', $eventKey)
            ->whereNotNull('domain_id')
            ->selectRaw('domain_id, SUM(total_count) as value')
            ->groupBy('domain_id')
            ->orderByDesc('value')
            ->limit($limit)
            ->get()
            ->map(fn (AnalyticsDailyMetric $metric): array => [
                'label' => Domain::query()->whereKey($metric->domain_id)->value('domain_name') ?? 'Domain '.$metric->domain_id,
                'value' => (int) $metric->value,
            ])
            ->all();
    }

    /** @param array{from: Carbon, to: Carbon} $range @param array<string, mixed> $filters @return array<int, array{label: string, value: int}> */
    public function topLanguages(array $range, array $filters = [], int $limit = 5): array
    {
        return $this->base($range, $filters)
            ->whereNotNull('locale_id')
            ->selectRaw('locale_id, SUM(total_count) as value')
            ->groupBy('locale_id')
            ->orderByDesc('value')
            ->limit($limit)
            ->get()
            ->map(fn (AnalyticsDailyMetric $metric): array => [
                'label' => Locale::query()->whereKey($metric->locale_id)->value('locale') ?? 'Locale '.$metric->locale_id,
                'value' => (int) $metric->value,
            ])
            ->all();
    }

    /** @param array{from: Carbon, to: Carbon} $range @param array<string, mixed> $filters */
    public function visitors(array $range, array $filters = []): int
    {
        return (int) $this->base($range, $filters)->sum('unique_visitors');
    }

    /** @param array{from: Carbon, to: Carbon} $range @param array<string, mixed> $filters */
    private function base(array $range, array $filters = []): Builder
    {
        return AnalyticsDailyMetric::query()
            ->whereDate('metric_date', '>=', $range['from']->toDateString())
            ->whereDate('metric_date', '<=', $range['to']->toDateString())
            ->when(($filters['locale_id'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('locale_id', $filters['locale_id']))
            ->when(($filters['domain_id'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('domain_id', $filters['domain_id']));
    }

    /** @return Collection<int, string> */
    private function dateBuckets(Carbon $from, Carbon $to)
    {
        $dates = collect();
        $cursor = $from->copy()->startOfDay();

        while ($cursor->lessThanOrEqualTo($to)) {
            $dates->push($cursor->toDateString());
            $cursor->addDay();
        }

        return $dates;
    }
}
