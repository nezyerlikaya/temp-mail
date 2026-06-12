<?php

namespace App\Actions\Sections;

use App\Models\Section;
use App\Models\SectionItem;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Validation\ValidationException;

class UpdateSectionItemAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, Section $section, SectionItem $item, array $payload): SectionItem
    {
        $this->assertBelongsToSection($section, $item);
        $item->update($payload);

        $this->audit->record('section_item.updated', $actor, null, [
            'section_id' => $section->id,
            'section_item_id' => $item->id,
        ], ['module' => 'sections', 'action' => 'Update section item', 'target' => $item]);

        return $item->refresh();
    }

    private function assertBelongsToSection(Section $section, SectionItem $item): void
    {
        if ($item->section_id !== $section->id) {
            throw ValidationException::withMessages(['section_item' => 'This item does not belong to the selected section.']);
        }
    }
}
