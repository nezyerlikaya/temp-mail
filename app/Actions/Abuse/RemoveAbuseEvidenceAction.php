<?php

namespace App\Actions\Abuse;

use App\Models\AbuseEvidence;
use App\Models\AbuseReport;
use App\Models\User;
use App\Services\Abuse\AbuseEvidenceService;

class RemoveAbuseEvidenceAction
{
    public function __construct(private readonly AbuseEvidenceService $evidence) {}

    public function handle(User $actor, AbuseReport $report, AbuseEvidence $evidence): void
    {
        $this->evidence->remove($actor, $report, $evidence);
    }
}
