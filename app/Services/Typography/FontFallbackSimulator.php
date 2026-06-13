<?php

namespace App\Services\Typography;

use App\Models\FontAssignment;
use App\Models\FontFamily;

class FontFallbackSimulator
{
    public function __construct(private readonly FontStackResolver $resolver) {}

    /** @param array<string, array<string, mixed>> $resolvedStacks */
    public function simulate(array $resolvedStacks, string $theme, ?string $locale = null): array
    {
        return collect($resolvedStacks)->map(function (array $stack, string $usage) use ($theme, $locale): array {
            $assignment = $this->resolver->effectiveAssignment($theme, $locale, $usage);

            return [
                'usage' => $usage,
                'primary' => $stack['font'] instanceof FontFamily ? $stack['font']->name : 'System fallback',
                'fallback_stack' => $assignment instanceof FontAssignment ? $assignment->fallback_stack : [],
                'simulated_stack' => $this->withoutPrimary($assignment),
                'has_fallback' => $assignment instanceof FontAssignment && $assignment->fallback_stack !== [],
            ];
        })->all();
    }

    private function withoutPrimary(?FontAssignment $assignment): string
    {
        $fallbacks = $assignment?->fallback_stack ?: ['system-sans', 'ui-sans-serif', 'sans-serif'];

        return collect($fallbacks)->map(function (string $fallback): string {
            $family = FontFamily::query()->where('slug', $fallback)->first();
            $cssFamily = $family?->css_family ?? $fallback;

            return str_contains($cssFamily, ' ') ? "'".$cssFamily."'" : $cssFamily;
        })->unique()->implode(', ');
    }
}
