<?php

namespace App\Actions\Sections;

use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReorderSectionsAction
{
    /** @param array<int, int> $order */
    public function handle(int $localeId, string $placement, array $order): void
    {
        $sections = Section::query()->whereIn('id', $order)->get();

        if ($sections->count() !== count($order)
            || $sections->contains(fn (Section $section): bool => $section->locale_id !== $localeId || $section->placement !== $placement)) {
            throw ValidationException::withMessages([
                'order' => 'Sections can only be reordered within the same language and placement.',
            ]);
        }

        DB::transaction(function () use ($order): void {
            foreach ($order as $index => $id) {
                Section::query()->whereKey($id)->update(['sort_order' => $index]);
            }
        });
    }
}
