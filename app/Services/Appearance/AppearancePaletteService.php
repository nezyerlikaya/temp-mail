<?php

namespace App\Services\Appearance;

class AppearancePaletteService
{
    /** @return array<int, array{label: string, value: string, purpose: string}> */
    public function suggestions(string $brandColor): array
    {
        return [
            ['label' => 'Brand', 'value' => $this->normalize($brandColor), 'purpose' => 'Primary actions'],
            ['label' => 'Soft brand', 'value' => $this->mix($brandColor, '#ffffff', 82), 'purpose' => 'Subtle surfaces'],
            ['label' => 'Deep brand', 'value' => $this->mix($brandColor, '#000000', 24), 'purpose' => 'High contrast controls'],
            ['label' => 'Cool accent', 'value' => $this->rotate($brandColor, 24), 'purpose' => 'Links and highlights'],
        ];
    }

    private function normalize(string $hex): string
    {
        return '#'.strtolower(ltrim($hex, '#'));
    }

    private function mix(string $hex, string $with, int $percent): string
    {
        [$r1, $g1, $b1] = $this->rgb($hex);
        [$r2, $g2, $b2] = $this->rgb($with);
        $weight = $percent / 100;

        return sprintf('#%02x%02x%02x',
            (int) round(($r1 * (1 - $weight)) + ($r2 * $weight)),
            (int) round(($g1 * (1 - $weight)) + ($g2 * $weight)),
            (int) round(($b1 * (1 - $weight)) + ($b2 * $weight)),
        );
    }

    private function rotate(string $hex, int $amount): string
    {
        [$r, $g, $b] = $this->rgb($hex);

        return sprintf('#%02x%02x%02x',
            min(255, max(0, $b + $amount)),
            min(255, max(0, $r - (int) round($amount / 2))),
            min(255, max(0, $g + (int) round($amount / 2))),
        );
    }

    /** @return array{int, int, int} */
    private function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
