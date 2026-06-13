<?php

namespace App\Services\Dashboard;

use App\Models\User;

class DashboardLivePayloadFactory
{
    public function __construct(
        private readonly DashboardSummaryService $summary,
        private readonly DashboardCriticalAlertService $criticalAlerts,
        private readonly DashboardRefreshSettingsResolver $settings,
    ) {}

    /** @return array<string, mixed> */
    public function make(User $user): array
    {
        $summary = $this->summary->summary($user);
        $settings = $this->settings->settings();

        return [
            'metrics' => collect($summary['metrics'])->values()->all(),
            'alerts' => $this->criticalAlerts->alerts($user),
            'last_updated' => $summary['last_updated']->toIso8601String(),
            'last_updated_display' => $summary['last_updated']->format('H:i:s'),
            'cache_seconds' => $summary['cache_seconds'],
            'stale_after_seconds' => $settings['stale_after_seconds'],
            'allowed_intervals' => $settings['allowed_intervals'],
            'default_interval' => $settings['default_interval'],
        ];
    }
}
