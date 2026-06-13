<?php

namespace App\Services\Abuse;

use App\Models\AbuseReport;
use Illuminate\Support\Str;

class AbuseCaseReferenceService
{
    public function generate(): string
    {
        do {
            $reference = 'AB-'.now()->format('ymd').'-'.Str::upper(Str::random(12));
        } while (AbuseReport::query()->where('case_reference', $reference)->exists());

        return $reference;
    }
}
