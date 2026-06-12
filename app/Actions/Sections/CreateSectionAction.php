<?php

namespace App\Actions\Sections;

use App\Models\Section;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class CreateSectionAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, array $payload): Section
    {
        $payload['created_by'] = $actor->id;
        $payload['updated_by'] = $actor->id;
        $payload['settings'] = $payload['settings'] ?? ['readiness' => 'foundation'];

        $section = Section::query()->create($payload);

        $this->audit->record('section.created', $actor, null, [
            'section_id' => $section->id,
            'locale_id' => $section->locale_id,
            'section_type' => $section->section_type,
            'placement' => $section->placement,
            'status' => $section->status,
        ], ['module' => 'sections', 'action' => 'Create section', 'target' => $section]);

        return $section;
    }
}
