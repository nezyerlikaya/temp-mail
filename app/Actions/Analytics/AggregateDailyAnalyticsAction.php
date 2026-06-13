<?php

namespace App\Actions\Analytics;

use App\Services\Analytics\AnalyticsAggregationService;
use Illuminate\Support\Carbon;

class AggregateDailyAnalyticsAction
{
    public function __construct(private readonly AnalyticsAggregationService $aggregation) {}

    /** @return array{date: string, rows: int} */
    public function handle(Carbon|string|null $date = null): array
    {
        return $this->aggregation->aggregate($date);
    }
}
