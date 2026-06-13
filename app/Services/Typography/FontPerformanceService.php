<?php

namespace App\Services\Typography;

use App\Models\FontFamily;

class FontPerformanceService
{
    /** @param array<string, array<string, mixed>> $resolvedStacks */
    public function summary(array $resolvedStacks): array
    {
        $families = collect($resolvedStacks)
            ->pluck('font')
            ->filter(fn ($font): bool => $font instanceof FontFamily)
            ->unique(fn (FontFamily $font): string => $font->slug)
            ->values();

        $weights = $families->flatMap(fn (FontFamily $font): array => $font->available_weights ?? [])->unique()->values();
        $estimatedKb = $families->sum(fn (FontFamily $font): int => $this->estimatedWeightKb($font));
        $warnings = [];

        if ($families->count() > 3) {
            $warnings[] = ['level' => 'warning', 'message' => 'More than three font families resolve on this page. Consider consolidating UI, heading, and body fonts.'];
        }

        if ($weights->count() > 5) {
            $warnings[] = ['level' => 'warning', 'message' => 'Excessive weight variants are enabled across resolved families. Keep only the weights the public UI needs.'];
        }

        foreach ($families as $font) {
            if (! in_array($font->font_display, ['swap', 'optional'], true)) {
                $warnings[] = ['level' => 'warning', 'message' => $font->name.' uses an unsupported font-display strategy. Use swap or optional.'];
            }
        }

        return [
            'family_count' => $families->count(),
            'weight_count' => $weights->count(),
            'estimated_kb' => $estimatedKb,
            'readiness' => $estimatedKb <= 240 && $families->count() <= 3 ? 'Ready' : 'Needs review',
            'warnings' => $warnings,
            'families' => $families->map(fn (FontFamily $font): array => [
                'name' => $font->name,
                'estimated_kb' => $this->estimatedWeightKb($font),
                'ready' => $font->provider === 'system' || $font->local_file_ready || $font->media_ready,
            ])->all(),
        ];
    }

    private function estimatedWeightKb(FontFamily $font): int
    {
        if ($font->provider === 'system') {
            return 0;
        }

        return max(35, count($font->available_weights ?? []) * 38);
    }
}
