<?php

namespace App\Actions\Notifications;

use App\Models\SystemNotification;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class ArchiveNotificationAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handle(User $actor, SystemNotification $notification): SystemNotification
    {
        if ($notification->archived_at === null) {
            $notification->forceFill([
                'archived_at' => now(),
                'read_at' => $notification->read_at ?? now(),
            ])->save();
        }

        $this->audit->record('notification.archived', $actor, $actor, [
            'event_key' => $notification->event_key,
            'notification_id' => $notification->getKey(),
        ], [
            'module' => 'notifications',
            'action' => 'Archived',
            'severity' => $notification->severity === 'critical' ? 'warning' : 'info',
            'target' => $notification,
        ]);

        return $notification;
    }
}
