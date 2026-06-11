<?php

namespace App\Services\Health;

class HealthNotificationDispatcher
{
    /** @param array{overall_status: string, summary: array<string, int>} $payload */
    public function readiness(array $payload): array
    {
        return [
            'notification_ready' => $payload['overall_status'] === 'critical',
            'critical_count' => $payload['summary']['critical'] ?? 0,
        ];
    }
}
