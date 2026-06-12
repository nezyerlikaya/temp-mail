<?php

namespace App\Services\Seo;

use App\Models\Locale;
use App\Models\SeoRecord;
use Illuminate\Support\Collection;

class HreflangReadinessService
{
    /**
     * @return array{locales: Collection<int, Locale>, rows: Collection<int, array<string, mixed>>, conflicts: array<int, array<string, mixed>>}
     */
    public function matrix(): array
    {
        $locales = Locale::query()->orderByDesc('is_default')->orderBy('sort_order')->orderBy('language_name')->get();
        $records = SeoRecord::query()->with('locale')->get();
        $groups = $records->groupBy(fn (SeoRecord $record): string => $record->target_type.'|'.$this->baseKey($record->target_key));

        $rows = $groups->map(function (Collection $group, string $key) use ($locales): array {
            $first = $group->first();
            [$targetType] = explode('|', $key, 2);
            $coverage = $locales->mapWithKeys(fn (Locale $locale): array => [
                $locale->locale => $group->contains(fn (SeoRecord $record): bool => $record->locale_id === $locale->id && filled($record->canonical_url)),
            ]);

            return [
                'target_type' => $targetType,
                'target_key' => $first?->target_key ?? '',
                'label' => $this->label($first),
                'coverage' => $coverage,
                'ready_count' => $coverage->filter()->count(),
                'total_count' => $locales->count(),
            ];
        })->values();

        return [
            'locales' => $locales,
            'rows' => $rows,
            'conflicts' => $this->conflicts($records),
        ];
    }

    private function baseKey(string $targetKey): string
    {
        return preg_replace('/:\d+$/', ':content', $targetKey) ?: $targetKey;
    }

    private function label(?SeoRecord $record): string
    {
        if (! $record) {
            return 'SEO target';
        }

        return str($record->target_type)->replace('_', ' ')->headline().' / '.$record->target_key;
    }

    /**
     * @param  Collection<int, SeoRecord>  $records
     * @return array<int, array<string, mixed>>
     */
    private function conflicts(Collection $records): array
    {
        return $records
            ->filter(fn (SeoRecord $record): bool => filled($record->canonical_url)
                && $record->locale
                && ! str_contains((string) $record->canonical_url, '/'.$record->locale->locale))
            ->map(fn (SeoRecord $record): array => [
                'severity' => 'warning',
                'type' => 'hreflang_canonical_conflict',
                'title' => 'Hreflang/canonical conflict',
                'message' => 'Canonical URL may not match '.$record->locale->language_name.'.',
                'record' => $record,
            ])
            ->values()
            ->all();
    }
}
