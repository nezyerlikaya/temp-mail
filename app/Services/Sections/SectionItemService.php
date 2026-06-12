<?php

namespace App\Services\Sections;

use App\Models\Section;
use App\Models\SectionItem;
use Illuminate\Support\Collection;

class SectionItemService
{
    /** @return array<string, string> */
    public function statuses(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'removed' => 'Removed',
        ];
    }

    /** @return Collection<int, SectionItem> */
    public function editableItems(Section $section): Collection
    {
        return $section->items()
            ->where('status', '!=', 'removed')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function nextSortOrder(Section $section): int
    {
        return ((int) $section->items()->max('sort_order')) + 1;
    }
}
