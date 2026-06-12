<?php

namespace App\Actions\Sections;

use App\Models\Section;
use App\Models\SectionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReorderSectionItemsAction
{
    /** @param array<int, int> $order */
    public function handle(Section $section, array $order): void
    {
        $items = SectionItem::query()->whereIn('id', $order)->get();

        if ($items->count() !== count($order)
            || $items->contains(fn (SectionItem $item): bool => $item->section_id !== $section->id || $item->status === 'removed')) {
            throw ValidationException::withMessages([
                'order' => 'Items can only be reordered within their own section.',
            ]);
        }

        DB::transaction(function () use ($order): void {
            foreach ($order as $index => $id) {
                SectionItem::query()->whereKey($id)->update(['sort_order' => $index]);
            }
        });
    }
}
