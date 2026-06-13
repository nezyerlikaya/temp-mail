<?php

namespace App\Services\Typography;

use App\Models\FontAssignment;
use App\Models\FontFamily;

class FontStackResolver
{
    public function __construct(
        private readonly FontAssignmentService $assignments,
        private readonly FontRegistry $registry,
    ) {}

    /** @return array{theme: string, locale: string|null, stacks: array<string, array<string, mixed>>, variables: array<string, string>, inline_style: string} */
    public function resolve(string $theme, ?string $locale = null): array
    {
        $families = $this->assignments->activeFamilies();
        $stacks = [];
        $variables = [];

        foreach (array_keys($this->registry->usageScopes()) as $usage) {
            $assignment = $this->effectiveAssignment($theme, $locale, $usage);
            $family = $assignment ? $families->get($assignment->font_family_slug) : null;
            $cssStack = $this->stackFor($family, $assignment);

            $stacks[$usage] = [
                'usage' => $usage,
                'source' => $assignment?->scope ?? 'fallback',
                'scope_key' => $assignment?->scope_key,
                'font' => $family,
                'stack' => $cssStack,
                'font_display' => $family?->font_display ?? 'swap',
            ];
            $variables['--tm-font-'.$usage] = $cssStack;
        }

        return [
            'theme' => $theme,
            'locale' => $locale,
            'stacks' => $stacks,
            'variables' => $variables,
            'inline_style' => $this->inlineStyle($variables),
        ];
    }

    public function effectiveAssignment(string $theme, ?string $locale, string $usage): ?FontAssignment
    {
        if ($locale) {
            $localeAssignment = $this->assignments->assignmentFor('locale', $locale, $usage);

            if ($localeAssignment) {
                return $localeAssignment;
            }
        }

        return $this->assignments->assignmentFor('theme', $theme, $usage)
            ?? $this->assignments->assignmentFor('global', 'default', $usage);
    }

    /** @param array<string, string> $variables */
    public function inlineStyle(array $variables): string
    {
        return collect($variables)
            ->map(fn (string $value, string $key): string => $key.': '.$value.';')
            ->implode(' ');
    }

    private function stackFor(?FontFamily $family, ?FontAssignment $assignment): string
    {
        $parts = [];

        if ($family) {
            $parts[] = $this->cssFamily($family->css_family);
        }

        foreach ($assignment?->fallback_stack ?? ['system-sans', 'ui-sans-serif', 'sans-serif'] as $fallback) {
            $fallbackFamily = FontFamily::query()->where('slug', $fallback)->first();
            $parts[] = $fallbackFamily ? $this->cssFamily($fallbackFamily->css_family) : $this->cssFamily($fallback);
        }

        return collect($parts)->unique()->implode(', ');
    }

    private function cssFamily(string $family): string
    {
        if (preg_match('/^[a-z-]+$/i', $family) === 1 || str_contains($family, ' ')) {
            return str_contains($family, ' ') ? "'".$family."'" : $family;
        }

        return 'sans-serif';
    }
}
