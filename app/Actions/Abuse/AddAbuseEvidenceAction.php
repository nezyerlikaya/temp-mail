<?php

namespace App\Actions\Abuse;

use App\Models\AbuseEvidence;
use App\Models\AbuseReport;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Abuse\AbuseEvidenceService;

class AddAbuseEvidenceAction
{
    public function __construct(private readonly AbuseEvidenceService $evidence) {}

    public function handle(User $actor, AbuseReport $report, MediaAsset $asset, ?string $label): AbuseEvidence
    {
        return $this->evidence->add($actor, $report, $asset, $label);
    }
}
