<?php

namespace App\Services\Sections;

use App\Models\Section;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class SectionAuditLogger
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $extra */
    public function record(string $event, string $action, User $actor, Section $section, array $extra = []): void
    {
        $this->audit->record($event, $actor, null, [
            'section_id' => $section->id,
            'locale_id' => $section->locale_id,
            'section_type' => $section->section_type,
            'placement' => $section->placement,
            'status' => $section->status,
            ...$extra,
        ], ['module' => 'sections', 'action' => $action, 'target' => $section]);
    }

    /** @param array<string, mixed> $metadata */
    public function recordDeleted(User $actor, array $metadata): void
    {
        $this->audit->record('section.permanently_deleted', $actor, null, $metadata, [
            'module' => 'sections',
            'action' => 'Permanently delete section',
            'target_type' => Section::class,
            'target_id' => $metadata['section_id'] ?? null,
        ]);
    }
}
