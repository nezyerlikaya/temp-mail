<?php

namespace App\Services\Sections;

use App\Models\Section;
use App\Models\SectionItem;
use Illuminate\Support\Collection;

class FaqQualityService
{
    /** @return array{active_count: int, state: string, message: string, duplicate_questions: array<int, string>} */
    public function forSection(Section $section): array
    {
        $items = $section->relationLoaded('items') ? $section->items : $section->items()->get();

        return $this->evaluate($items);
    }

    /**
     * @param  Collection<int, SectionItem>  $items
     * @return array{active_count: int, state: string, message: string, duplicate_questions: array<int, string>}
     */
    public function evaluate(Collection $items): array
    {
        $active = $items->where('status', 'active');
        $count = $active->count();
        $duplicates = $active
            ->groupBy(fn ($item): string => str((string) $item->title)->lower()->squish()->toString())
            ->filter(fn (Collection $group): bool => $group->count() > 1)
            ->flatMap(fn (Collection $group): array => $group->pluck('title')->all())
            ->unique()
            ->values()
            ->all();

        [$state, $message] = match (true) {
            $count < 4 => ['warning', 'Add at least 4 active questions.'],
            $count > 12 => ['warning', 'Reduce active questions to 12 or fewer.'],
            $count >= 6 && $count <= 8 => ['ideal', 'Ideal FAQ coverage: 6-8 active questions.'],
            default => ['ready', 'FAQ count is within the recommended range.'],
        };

        return [
            'active_count' => $count,
            'state' => $duplicates !== [] ? 'warning' : $state,
            'message' => $duplicates !== [] ? 'Duplicate active questions need review.' : $message,
            'duplicate_questions' => $duplicates,
        ];
    }
}
