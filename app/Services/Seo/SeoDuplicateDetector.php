<?php

namespace App\Services\Seo;

use App\Models\SeoRecord;
use Illuminate\Support\Collection;

class SeoDuplicateDetector
{
    /**
     * @return Collection<int, array{field: string, value: string, count: int, records: Collection<int, SeoRecord>}>
     */
    public function duplicates(string $field): Collection
    {
        if (! in_array($field, ['meta_title', 'meta_description'], true)) {
            return collect();
        }

        return SeoRecord::query()
            ->with('locale')
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->get()
            ->groupBy(fn (SeoRecord $record): string => (string) $record->{$field})
            ->filter(fn (Collection $records): bool => $records->count() > 1)
            ->map(fn (Collection $records, string $value): array => [
                'field' => $field,
                'value' => $value,
                'count' => $records->count(),
                'records' => $records->values(),
            ])
            ->values();
    }
}
