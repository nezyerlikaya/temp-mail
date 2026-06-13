<?php

namespace App\Services\Translations;

use App\Models\Locale;
use App\Models\TranslationSource;
use Illuminate\Support\Facades\Schema;
use Throwable;

class TranslationCoverageService
{
    /** @return array{total: int, completed: int, coverage: int, required_total: int, required_completed: int, required_coverage: int, reviewed: int, published: int, missing: int, publish_ready: bool} */
    public function forLocale(Locale $locale): array
    {
        if ($locale->locale === 'en') {
            return [
                'total' => 0,
                'completed' => 0,
                'coverage' => 100,
                'required_total' => 0,
                'required_completed' => 0,
                'required_coverage' => 100,
                'reviewed' => 0,
                'published' => 0,
                'missing' => 0,
                'publish_ready' => true,
            ];
        }

        try {
            if (! Schema::hasTable('translation_sources') || ! Schema::hasTable('translation_values')) {
                return $this->empty();
            }

            $sources = TranslationSource::query()
                ->where('is_active', true)
                ->with(['values' => fn ($query) => $query->where('locale_id', $locale->id)])
                ->get();

            $completed = $sources->filter(fn (TranslationSource $source): bool => filled($source->values->first()?->value))->count();
            $required = $sources->where('is_required', true);
            $requiredCompleted = $required->filter(fn (TranslationSource $source): bool => filled($source->values->first()?->value))->count();
            $reviewed = $sources->filter(fn (TranslationSource $source): bool => $source->values->first()?->status === 'reviewed')->count();
            $published = $sources->filter(fn (TranslationSource $source): bool => $source->values->first()?->status === 'published')->count();
            $requiredPublishReady = $required->every(fn (TranslationSource $source): bool => in_array($source->values->first()?->status, ['reviewed', 'published'], true));

            return [
                'total' => $sources->count(),
                'completed' => $completed,
                'coverage' => $this->percent($completed, $sources->count()),
                'required_total' => $required->count(),
                'required_completed' => $requiredCompleted,
                'required_coverage' => $this->percent($requiredCompleted, $required->count()),
                'reviewed' => $reviewed,
                'published' => $published,
                'missing' => $sources->count() - $completed,
                'publish_ready' => $required->isNotEmpty() && $requiredPublishReady,
            ];
        } catch (Throwable) {
            return $this->empty();
        }
    }

    /** @return array<string, array{label: string, total: int, completed: int, coverage: int}> */
    public function byGroup(Locale $locale, array $groups): array
    {
        return collect($groups)->mapWithKeys(function (string $label, string $group) use ($locale): array {
            $sources = TranslationSource::query()
                ->where('is_active', true)
                ->where('group_key', $group)
                ->with(['values' => fn ($query) => $query->where('locale_id', $locale->id)])
                ->get();
            $completed = $sources->filter(fn (TranslationSource $source): bool => filled($source->values->first()?->value))->count();

            return [$group => [
                'label' => $label,
                'total' => $sources->count(),
                'completed' => $completed,
                'coverage' => $this->percent($completed, $sources->count()),
            ]];
        })->all();
    }

    private function percent(int $completed, int $total): int
    {
        return $total === 0 ? 100 : (int) round(($completed / $total) * 100);
    }

    /** @return array{total: int, completed: int, coverage: int, required_total: int, required_completed: int, required_coverage: int, reviewed: int, published: int, missing: int, publish_ready: bool} */
    private function empty(): array
    {
        return [
            'total' => 0,
            'completed' => 0,
            'coverage' => 0,
            'required_total' => 0,
            'required_completed' => 0,
            'required_coverage' => 0,
            'reviewed' => 0,
            'published' => 0,
            'missing' => 0,
            'publish_ready' => false,
        ];
    }
}
