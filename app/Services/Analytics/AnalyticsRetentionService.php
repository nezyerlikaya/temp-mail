<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsEvent;

class AnalyticsRetentionService
{
    public function rawEventRetentionDays(): int
    {
        return 90;
    }

    public function purgeExpiredRawEvents(): int
    {
        return AnalyticsEvent::query()
            ->where('created_at', '<', now()->subDays($this->rawEventRetentionDays()))
            ->delete();
    }

    /** @return array<string, mixed> */
    public function readiness(): array
    {
        return [
            'raw_event_retention_days' => $this->rawEventRetentionDays(),
            'aggregate_storage' => 'analytics_daily_metrics',
            'exports_audited_later' => true,
        ];
    }
}
