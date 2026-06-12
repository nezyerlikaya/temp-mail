<?php

namespace App\Actions\Notifications;

use App\Models\SystemNotification;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class MarkNotificationReadAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handle(User $actor, SystemNotification $notification): SystemNotification
    {
        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        if ($notification->severity === 'critical') {
            $this->audit->record('notification.marked_read', $actor, $actor, [
                'event_key' => $notification->event_key,
                'notification_id' => $notification->getKey(),
            ], [
                'module' => 'notifications',
                'action' => 'Marked read',
                'severity' => 'info',
                'target' => $notification,
            ]);
        }

        return $notification;
    }
}
