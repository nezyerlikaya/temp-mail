<?php

namespace App\Actions\Sections;

use App\Models\Section;
use App\Models\SectionItem;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Sections\SectionItemService;

class CreateSectionItemAction
{
    public function __construct(
        private readonly SectionItemService $items,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, Section $section, array $payload): SectionItem
    {
        $payload['sort_order'] = $payload['sort_order'] ?? $this->items->nextSortOrder($section);
        $item = $section->items()->create($payload);

        $this->audit->record('section_item.created', $actor, null, [
            'section_id' => $section->id,
            'section_item_id' => $item->id,
        ], ['module' => 'sections', 'action' => 'Create section item', 'target' => $item]);

        return $item;
    }
}
