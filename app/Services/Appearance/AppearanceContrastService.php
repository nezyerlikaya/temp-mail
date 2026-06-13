<?php

namespace App\Services\Appearance;

class AppearanceContrastService
{
    /** @param array<string, string> $tokens @return array{checks: array<int, array{name: string, foreground: string, background: string, ratio: float, status: string, critical: bool, message: string}>, summary: array{passes: int, warnings: int, failures: int, critical_failures: int, publishable: bool}} */
    public function report(array $tokens): array
    {
        $checks = [
            $this->check('Text / background', $tokens['text_color'], $tokens['background_color'], true),
            $this->check('Muted text / background', $tokens['muted_text_color'], $tokens['background_color'], false),
            $this->check('Button text / brand color', '#ffffff', $tokens['brand_color'], true),
            $this->check('Link / background', $tokens['accent_color'], $tokens['background_color'], true),
        ];

        $failures = collect($checks)->where('status', 'fail')->count();
        $criticalFailures = collect($checks)->where('critical', true)->where('status', 'fail')->count();
        $warnings = collect($checks)->where('status', 'warning')->count();

        return [
            'checks' => $checks,
            'summary' => [
                'passes' => collect($checks)->where('status', 'pass')->count(),
                'warnings' => $warnings,
                'failures' => $failures,
                'critical_failures' => $criticalFailures,
                'publishable' => $criticalFailures === 0,
            ],
        ];
    }

    /** @param array<string, string> $tokens */
    public function hasCriticalFailures(array $tokens): bool
    {
        return $this->report($tokens)['summary']['critical_failures'] > 0;
    }

    /** @return array{name: string, foreground: string, background: string, ratio: float, status: string, critical: bool, message: string} */
    private function check(string $name, string $foreground, string $background, bool $critical): array
    {
        $ratio = $this->ratio($foreground, $background);
        $status = match (true) {
            $ratio >= 4.5 => 'pass',
            $ratio >= 3.0 => 'warning',
            default => 'fail',
        };

        return [
            'name' => $name,
            'foreground' => $foreground,
            'background' => $background,
            'ratio' => round($ratio, 2),
            'status' => $status,
            'critical' => $critical,
            'message' => match ($status) {
                'pass' => 'Meets the standard text contrast target.',
                'warning' => 'Usable for large text or supporting UI, but should be improved.',
                default => 'Critical contrast failure. Publishing is blocked until this improves.',
            },
        ];
    }

    private function ratio(string $foreground, string $background): float
    {
        $lighter = max($this->luminance($foreground), $this->luminance($background));
        $darker = min($this->luminance($foreground), $this->luminance($background));

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    private function luminance(string $hex): float
    {
        [$r, $g, $b] = $this->rgb($hex);

        $channels = array_map(function (int $value): float {
            $value = $value / 255;

            return $value <= 0.03928
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        }, [$r, $g, $b]);

        return (0.2126 * $channels[0]) + (0.7152 * $channels[1]) + (0.0722 * $channels[2]);
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
