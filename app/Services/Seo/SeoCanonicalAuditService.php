<?php

namespace App\Services\Seo;

use App\Models\SeoRecord;

class SeoCanonicalAuditService
{
    public function __construct(private readonly SeoCanonicalValidator $validator) {}

    /** @return array<int, array<string, mixed>> */
    public function issues(): array
    {
        return SeoRecord::query()
            ->with('locale')
            ->get()
            ->flatMap(function (SeoRecord $record): array {
                $message = $this->validator->message((string) $record->canonical_url);

                if ($message !== null) {
                    return [[
                        'severity' => 'critical',
                        'type' => 'invalid_canonical',
                        'title' => 'Invalid canonical URL',
                        'message' => $message,
                        'record' => $record,
                    ]];
                }

                if ($record->canonical_url && $record->locale && ! str_contains((string) $record->canonical_url, '/'.$record->locale->locale)) {
                    return [[
                        'severity' => 'warning',
                        'type' => 'canonical_locale_conflict',
                        'title' => 'Canonical may point outside the selected language',
                        'message' => 'The canonical URL does not appear to include the record language path.',
                        'record' => $record,
                    ]];
                }

                return [];
            })
            ->values()
            ->all();
    }
}
