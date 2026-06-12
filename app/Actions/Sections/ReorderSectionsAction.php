<?php

namespace App\Actions\Sections;

use App\Models\Section;
use App\Models\User;
use App\Services\Sections\SectionAuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReorderSectionsAction
{
    public function __construct(private readonly SectionAuditLogger $audit) {}

    /** @param array<int, int> $order */
    public function handle(User $actor, int $localeId, string $placement, array $order): void
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

        $sections->each(function (Section $section) use ($actor, $order): void {
            $this->audit->record('section.reordered', 'Reorder section', $actor, $section, [
                'order' => $order,
            ]);
        });
    }
}
