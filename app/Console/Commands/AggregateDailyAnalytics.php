<?php

namespace App\Console\Commands;

use App\Actions\Analytics\AggregateDailyAnalyticsAction;
use Illuminate\Console\Command;

class AggregateDailyAnalytics extends Command
{
    protected $signature = 'analytics:aggregate-daily {date?}';

    protected $description = 'Aggregate privacy-friendly analytics events into daily metrics.';

    public function handle(AggregateDailyAnalyticsAction $action): int
    {
        $result = $action->handle($this->argument('date'));
        $this->info($result['date'].' aggregated with '.$result['rows'].' metric row(s).');

        return self::SUCCESS;
    }
}
