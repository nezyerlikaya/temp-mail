<?php

namespace App\Services\Typography;

use App\Models\FontFamily;
use App\Models\Locale;

class FontCoverageService
{
    /** @return array<int, string> */
    public function scriptsForLocale(Locale|string $locale): array
    {
        $code = $locale instanceof Locale ? $locale->locale : $locale;
        $direction = $locale instanceof Locale ? $locale->direction : null;
        $language = str($code)->before('-')->before('_')->lower()->toString();

        if ($language === 'ar') {
            return ['arabic'];
        }

        if (in_array($language, ['he', 'iw'], true)) {
            return ['hebrew'];
        }

        if ($language === 'el') {
            return ['greek'];
        }

        if (in_array($language, ['ru', 'uk', 'bg', 'sr', 'mk', 'be'], true)) {
            return ['cyrillic'];
        }

        if (in_array($language, ['tr', 'pl', 'cs', 'sk', 'ro', 'hu', 'vi', 'de', 'fr', 'es', 'pt'], true)) {
            return ['latin', 'latin_extended'];
        }

        if ($direction === 'rtl') {
            return ['arabic', 'hebrew'];
        }

        return ['latin'];
    }

    /**
     * @return array<int, array{level: string, message: string}>
     */
    public function warningsForAssignment(FontFamily $family, Locale|string $locale): array
    {
        $required = $this->scriptsForLocale($locale);
        $supported = $family->supported_scripts ?? [];
        $missing = collect($required)->reject(fn (string $script): bool => in_array($script, $supported, true))->values()->all();

        if ($missing === []) {
            return [];
        }

        $rtlMissing = array_intersect($missing, ['arabic', 'hebrew']);

        return [[
            'level' => $rtlMissing ? 'warning' : 'notice',
            'message' => $rtlMissing
                ? 'RTL readiness warning: this font does not cover '.implode(', ', $rtlMissing).'.'
                : 'Coverage notice: this font misses '.implode(', ', $missing).'.',
        ]];
    }

    /** @return array<string, mixed> */
    public function coverageSummary(FontFamily $family, Locale|string|null $locale = null): array
    {
        $required = $locale ? $this->scriptsForLocale($locale) : [];
        $supported = $family->supported_scripts ?? [];

        return [
            'supported' => $supported,
            'required' => $required,
            'missing' => collect($required)->reject(fn (string $script): bool => in_array($script, $supported, true))->values()->all(),
            'rtl_ready' => $family->rtl_support,
        ];
    }
}
