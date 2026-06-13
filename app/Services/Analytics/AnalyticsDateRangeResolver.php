<?php

namespace App\Services\Analytics;

use Illuminate\Support\Carbon;

class AnalyticsDateRangeResolver
{
    /** @param array<string, mixed> $filters @return array{preset: string, from: Carbon, to: Carbon, label: string} */
    public function resolve(array $filters): array
    {
        $preset = (string) ($filters['preset'] ?? 'last_7_days');

        if ($preset === 'custom') {
            $from = Carbon::parse($filters['date_from'] ?? today()->subDays(6))->startOfDay();
            $to = Carbon::parse($filters['date_to'] ?? today())->endOfDay();
        } else {
            [$from, $to] = match ($preset) {
                'today' => [today()->startOfDay(), today()->endOfDay()],
                'last_30_days' => [today()->subDays(29)->startOfDay(), today()->endOfDay()],
                default => [today()->subDays(6)->startOfDay(), today()->endOfDay()],
            };
        }

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [
            'preset' => in_array($preset, ['today', 'last_7_days', 'last_30_days', 'custom'], true) ? $preset : 'last_7_days',
            'from' => $from,
            'to' => $to,
            'label' => $from->toDateString().' to '.$to->toDateString(),
        ];
    }

    /** @return array<string, string> */
    public function presets(): array
    {
        return [
            'today' => 'Today',
            'last_7_days' => 'Last 7 days',
            'last_30_days' => 'Last 30 days',
            'custom' => 'Custom range',
        ];
    }
}
