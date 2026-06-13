<?php

namespace App\Actions\Abuse;

use App\Models\AbuseReport;
use App\Services\Abuse\AbuseNotificationDispatcher;
use App\Services\Abuse\AbuseReportStore;
use Illuminate\Http\Request;

class SubmitAbuseReportAction
{
    public function __construct(
        private readonly AbuseReportStore $store,
        private readonly AbuseNotificationDispatcher $notifications,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(array $payload, Request $request): AbuseReport
    {
        $report = $this->store->create($payload, $request);
        $this->notifications->dispatchNewCase($report);

        return $report;
    }
}
