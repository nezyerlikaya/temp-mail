<?php

namespace App\Services\Localization;

use App\Models\Locale;
use Illuminate\Support\Collection;

class LocaleReadinessService
{
    /**
     * @param  Collection<int, Locale>  $locales
     * @return array{total: int, active: int, passive: int, ready: int, launched: int, rtl: int, default_locale: string|null, issues: array<int, string>}
     */
    public function summary(Collection $locales): array
    {
        $default = $locales->firstWhere('is_default', true);
        $issues = [];

        if ($locales->where('is_default', true)->count() !== 1) {
            $issues[] = 'Exactly one default language is required.';
        }

        if ($default instanceof Locale && ! $default->is_active) {
            $issues[] = 'The default language must be active.';
        }

        if ($locales->where('direction', 'rtl')->whereNotIn('locale', ['ar', 'he'])->count() > 0) {
            $issues[] = 'Only validated RTL locales should use right-to-left direction.';
        }

        return [
            'total' => $locales->count(),
            'active' => $locales->where('is_active', true)->count(),
            'passive' => $locales->where('is_active', false)->count(),
            'ready' => $locales->where('market_readiness', 'ready')->count(),
            'launched' => $locales->where('launch_status', 'launched')->count(),
            'rtl' => $locales->where('direction', 'rtl')->count(),
            'default_locale' => $default?->locale,
            'issues' => $issues,
        ];
    }

    /** @return array{score: int, label: string, missing: array<int, string>} */
    public function forLocale(Locale $locale): array
    {
        $checks = [
            'Market readiness selected' => $locale->market_readiness !== 'planned',
            'Launch status prepared' => in_array($locale->launch_status, ['ready', 'launched'], true),
            'Direction confirmed' => in_array($locale->direction, ['ltr', 'rtl'], true),
            'Active for launch' => $locale->is_active,
        ];

        $passed = collect($checks)->filter()->count();
        $score = (int) round(($passed / count($checks)) * 100);

        return [
            'score' => $score,
            'label' => match (true) {
                $score >= 100 => 'Launch ready',
                $score >= 50 => 'Needs review',
                default => 'Planned',
            },
            'missing' => collect($checks)
                ->filter(fn (bool $passed): bool => ! $passed)
                ->keys()
                ->values()
                ->all(),
        ];
    }
}
