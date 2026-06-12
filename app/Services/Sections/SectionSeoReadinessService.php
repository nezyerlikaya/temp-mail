<?php

namespace App\Services\Sections;

use App\Models\Section;

class SectionSeoReadinessService
{
    public function __construct(private readonly FaqQualityService $faqQuality) {}

    /** @return array<string, mixed> */
    public function forSection(Section $section): array
    {
        if ($section->section_type !== 'faq') {
            return [
                'applies' => false,
                'state' => 'neutral',
                'schema_allowed' => false,
                'message' => 'FAQ schema rules only apply to FAQ sections.',
                'active_count' => null,
                'ideal' => false,
                'warnings' => [],
            ];
        }

        $quality = $this->faqQuality->forSection($section);
        $count = $quality['active_count'];
        $duplicates = $quality['duplicate_questions'];
        $schemaAllowed = $section->status === 'active' && $count >= 4 && $duplicates === [];
        $warnings = [];

        if ($count < 4) {
            $warnings[] = 'FAQ schema needs at least 4 active questions.';
        }

        if ($count > 12) {
            $warnings[] = 'Maximum recommended FAQ schema coverage is 12 active questions.';
        }

        if ($duplicates !== []) {
            $warnings[] = 'Duplicate active questions should be resolved before schema output.';
        }

        if ($section->status !== 'active') {
            $warnings[] = 'FAQ schema is only emitted for active sections.';
        }

        return [
            'applies' => true,
            'state' => $schemaAllowed ? ($count >= 6 && $count <= 8 ? 'ideal' : 'ready') : 'warning',
            'schema_allowed' => $schemaAllowed,
            'message' => $schemaAllowed
                ? ($count >= 6 && $count <= 8 ? 'Ideal FAQ schema readiness: 6-8 active questions.' : 'FAQ schema can be emitted.')
                : 'FAQ schema should stay off until quality rules pass.',
            'active_count' => $count,
            'ideal' => $count >= 6 && $count <= 8,
            'max_recommended' => 12,
            'warnings' => $warnings,
        ];
    }
}
