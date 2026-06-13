<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsDailyMetric;
use App\Models\AnalyticsEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsAggregationService
{
    /** @return array{date: string, rows: int} */
    public function aggregate(Carbon|string|null $date = null): array
    {
        $day = $date instanceof Carbon ? $date->copy()->startOfDay() : Carbon::parse($date ?? today())->startOfDay();
        $rows = 0;

        AnalyticsEvent::query()
            ->select([
                'event_key',
                'locale_id',
                'domain_id',
                DB::raw('COUNT(*) as total_count'),
                DB::raw('COUNT(DISTINCT COALESCE(visitor_hash, session_hash, user_id, id)) as unique_visitors'),
            ])
            ->whereBetween('created_at', [$day, $day->copy()->endOfDay()])
            ->groupBy('event_key', 'locale_id', 'domain_id')
            ->get()
            ->each(function (object $metric) use ($day, &$rows): void {
                AnalyticsDailyMetric::query()->updateOrCreate([
                    'metric_date' => $day->toDateString(),
                    'event_key' => $metric->event_key,
                    'locale_id' => $metric->locale_id,
                    'domain_id' => $metric->domain_id,
                ], [
                    'total_count' => (int) $metric->total_count,
                    'unique_visitors' => (int) $metric->unique_visitors,
                    'metadata' => ['source' => 'daily_aggregate'],
                ]);

                $rows++;
            });

        return ['date' => $day->toDateString(), 'rows' => $rows];
    }
}
