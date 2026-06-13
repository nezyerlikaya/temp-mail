<?php

namespace App\Services\Typography;

use App\Models\FontFamily;
use App\Models\Locale;
use Illuminate\Support\Collection;

class TypographyReadinessService
{
    public function __construct(
        private readonly FontCoverageService $coverage,
        private readonly FontStackResolver $resolver,
    ) {}

    /**
     * @param  Collection<int, Locale>  $locales
     * @return array<int, array<string, mixed>>
     */
    public function languageCards(Collection $locales, string $theme): array
    {
        return $locales->map(function (Locale $locale) use ($theme): array {
            $resolved = $this->resolver->resolve($theme, $locale->locale);
            $missing = collect($resolved['stacks'])
                ->flatMap(function (array $stack) use ($locale): array {
                    $font = $stack['font'] ?? null;

                    return $font instanceof FontFamily
                        ? $this->coverage->coverageSummary($font, $locale)['missing']
                        : ['unresolved'];
                })
                ->unique()
                ->values()
                ->all();

            return [
                'locale' => $locale,
                'status' => $missing === [] ? 'Ready' : 'Needs review',
                'missing' => $missing,
                'direction' => $locale->direction,
                'scripts' => $this->coverage->scriptsForLocale($locale),
            ];
        })->all();
    }

    /** @return array<string, mixed> */
    public function rtlSummary(Collection $locales, string $theme): array
    {
        $rtl = $locales->where('direction', 'rtl');
        $cards = collect($this->languageCards($rtl, $theme));

        return [
            'total' => $rtl->count(),
            'ready' => $cards->where('status', 'Ready')->count(),
            'needs_review' => $cards->where('status', 'Needs review')->count(),
        ];
    }
}
