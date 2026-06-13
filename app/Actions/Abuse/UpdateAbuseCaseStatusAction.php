<?php

namespace App\Actions\Abuse;

use App\Models\AbuseReport;
use App\Models\User;
use App\Services\Abuse\AbuseCaseService;

class UpdateAbuseCaseStatusAction
{
    public function __construct(private readonly AbuseCaseService $cases) {}

    public function handle(User $actor, AbuseReport $report, string $status): AbuseReport
    {
        return $this->cases->updateStatus($actor, $report, $status);
    }
}
