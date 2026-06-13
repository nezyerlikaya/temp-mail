<?php

namespace App\Actions\Abuse;

use App\Models\AbuseReport;
use App\Models\User;
use App\Services\Abuse\AbuseResolutionService;

class RejectAbuseCaseAction
{
    public function __construct(private readonly AbuseResolutionService $resolution) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, AbuseReport $report, array $payload): AbuseReport
    {
        return $this->resolution->reject($actor, $report, $payload);
    }
}
