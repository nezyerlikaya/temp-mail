<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsDailyMetric;

class AnalyticsFreshnessService
{
    /** @return array{last_aggregated_at: string|null, stale: bool, message: string} */
    public function status(): array
    {
        $latest = AnalyticsDailyMetric::query()->max('updated_at');
        $latestDate = AnalyticsDailyMetric::query()->max('metric_date');
        $stale = $latestDate === null || $latestDate < today()->toDateString();

        return [
            'last_aggregated_at' => $latest,
            'stale' => $stale,
            'message' => $stale
                ? 'Daily aggregates are not current yet. Run analytics:aggregate-daily after recent event ingestion.'
                : 'Daily aggregates are current for today.',
        ];
    }
}
