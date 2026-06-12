<?php

namespace App\Actions\Sections;

use App\Models\Section;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class UpdateSectionAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, Section $section, array $payload): Section
    {
        $payload['updated_by'] = $actor->id;
        $payload['settings'] = $payload['settings'] ?? $section->settings ?? ['readiness' => 'foundation'];

        $section->update($payload);

        $this->audit->record('section.updated', $actor, null, [
            'section_id' => $section->id,
            'locale_id' => $section->locale_id,
            'section_type' => $section->section_type,
            'placement' => $section->placement,
            'status' => $section->status,
        ], ['module' => 'sections', 'action' => 'Update section', 'target' => $section]);

        return $section->refresh();
    }
}
