<?php

namespace App\Actions\Abuse;

use App\Models\AbuseReport;
use App\Models\User;
use App\Services\Abuse\AbuseResolutionService;

class ArchiveAbuseCaseAction
{
    public function __construct(private readonly AbuseResolutionService $resolution) {}

    public function handle(User $actor, AbuseReport $report, string $reason): AbuseReport
    {
        return $this->resolution->archive($actor, $report, $reason);
    }
}
