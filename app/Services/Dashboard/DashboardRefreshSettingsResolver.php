<?php

namespace App\Services\Dashboard;

class DashboardRefreshSettingsResolver
{
    /** @return array{default_interval: int, allowed_intervals: array<int, int>, stale_after_seconds: int} */
    public function settings(): array
    {
        return [
            'default_interval' => 30,
            'allowed_intervals' => [15, 30, 60],
            'stale_after_seconds' => 120,
        ];
    }
}
