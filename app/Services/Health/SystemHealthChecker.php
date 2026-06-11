<?php

namespace App\Services\Health;

class SystemHealthChecker
{
    public function __construct(private readonly HealthCheckRegistry $registry) {}

    /** @return array{overall_status: string, summary: array{healthy: int, attention: int, critical: int}, results: array<int, array<string, string>>, checked_at: string} */
    public function run(): array
    {
        $results = $this->registry->checks();
        $summary = [
            'healthy' => collect($results)->where('status', 'healthy')->count(),
            'attention' => collect($results)->where('status', 'attention')->count(),
            'critical' => collect($results)->where('status', 'critical')->count(),
        ];

        $overall = match (true) {
            $summary['critical'] > 0 => 'critical',
            $summary['attention'] > 0 => 'attention',
            default => 'healthy',
        };

        return [
            'overall_status' => $overall,
            'summary' => $summary,
            'results' => $results,
            'checked_at' => now()->toIso8601String(),
        ];
    }
}
