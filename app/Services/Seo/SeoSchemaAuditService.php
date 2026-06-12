<?php

namespace App\Services\Seo;

use App\Models\SeoRecord;

class SeoSchemaAuditService
{
    public function __construct(private readonly SeoSchemaValidator $validator) {}

    /** @return array<int, array<string, mixed>> */
    public function issues(): array
    {
        return SeoRecord::query()
            ->with('locale')
            ->get()
            ->flatMap(function (SeoRecord $record): array {
                if (blank($record->schema_type)) {
                    return [[
                        'severity' => 'notice',
                        'type' => 'missing_schema',
                        'title' => 'Schema type missing',
                        'message' => 'Choose a schema type when this target is important for rich results.',
                        'record' => $record,
                    ]];
                }

                if ($record->schema_json === null) {
                    return [];
                }

                $json = json_encode($record->schema_json);
                $message = $this->validator->message(is_string($json) ? $json : '');

                return $message === null ? [] : [[
                    'severity' => 'critical',
                    'type' => 'invalid_schema',
                    'title' => 'Schema JSON-LD needs review',
                    'message' => $message,
                    'record' => $record,
                ]];
            })
            ->values()
            ->all();
    }
}
