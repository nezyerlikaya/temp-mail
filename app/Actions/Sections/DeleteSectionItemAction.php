<?php

namespace App\Actions\Sections;

use App\Models\Section;
use App\Models\SectionItem;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Validation\ValidationException;

class DeleteSectionItemAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handle(User $actor, Section $section, SectionItem $item): SectionItem
    {
        if ($item->section_id !== $section->id) {
            throw ValidationException::withMessages(['section_item' => 'This item does not belong to the selected section.']);
        }

        $item->update(['status' => 'removed']);

        $this->audit->record('section_item.removed', $actor, null, [
            'section_id' => $section->id,
            'section_item_id' => $item->id,
        ], ['module' => 'sections', 'action' => 'Remove section item', 'target' => $item]);

        return $item;
    }
}
