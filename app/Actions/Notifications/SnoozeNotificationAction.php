<?php

namespace App\Actions\Notifications;

use App\Models\SystemNotification;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class SnoozeNotificationAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handle(User $actor, SystemNotification $notification, string $duration): SystemNotification
    {
        $until = match ($duration) {
            '1_day' => now()->addDay(),
            default => now()->addHour(),
        };

        if ($notification->severity === 'critical') {
            $until = now();
        }

        $notification->forceFill(['snoozed_until' => $until])->save();

        $this->audit->record('notification.snoozed', $actor, $actor, [
            'event_key' => $notification->event_key,
            'duration' => $duration,
            'snoozed_until' => $until->toIso8601String(),
        ], [
            'module' => 'notifications',
            'action' => 'Snoozed',
            'severity' => $notification->severity === 'critical' ? 'warning' : 'info',
            'target' => $notification,
        ]);

        return $notification;
    }
}
