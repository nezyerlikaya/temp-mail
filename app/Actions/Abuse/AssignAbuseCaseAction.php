<?php

namespace App\Actions\Abuse;

use App\Models\AbuseReport;
use App\Models\User;
use App\Services\Abuse\AbuseCaseService;

class AssignAbuseCaseAction
{
    public function __construct(private readonly AbuseCaseService $cases) {}

    public function handle(User $actor, AbuseReport $report, ?User $assignee): AbuseReport
    {
        return $this->cases->assign($actor, $report, $assignee);
    }
}
