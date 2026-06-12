<?php

namespace App\Services\Sections;

use App\Models\Section;
use Illuminate\Support\Facades\URL;

class SectionPreviewService
{
    public function previewUrl(Section $section): string
    {
        return URL::temporarySignedRoute('admin.sections-studio.preview', now()->addMinutes(30), $section);
    }

    /** @return array<string, mixed> */
    public function readiness(Section $section): array
    {
        return [
            'preview_url' => $this->previewUrl($section),
            'signed' => true,
            'expires_in' => '30 minutes',
            'language' => $section->locale?->language_name,
            'locale' => $section->locale?->locale,
        ];
    }
}
