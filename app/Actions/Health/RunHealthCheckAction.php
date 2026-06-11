<?php

namespace App\Actions\Health;

use App\Models\SystemHealthCheck;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Health\HealthHistoryStore;
use App\Services\Health\HealthNotificationDispatcher;
use App\Services\Health\SystemHealthChecker;

class RunHealthCheckAction
{
    public function __construct(
        private readonly SystemHealthChecker $checker,
        private readonly HealthHistoryStore $history,
        private readonly HealthNotificationDispatcher $notifications,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor): SystemHealthCheck
    {
        $payload = $this->checker->run();
        $record = $this->history->record($payload, $actor);
        $notification = $this->notifications->readiness($payload);

        $this->audit->record('system.health_check_run', $actor, $actor, [
            'overall_status' => $record->overall_status,
            'summary' => $record->summary,
            ...$notification,
        ], [
            'module' => 'system',
            'action' => 'Health check run',
            'severity' => $record->overall_status === 'critical' ? 'warning' : 'info',
        ]);

        return $record;
    }
}
