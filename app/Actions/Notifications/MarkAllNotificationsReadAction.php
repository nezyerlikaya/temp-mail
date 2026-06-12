<?php

namespace App\Actions\Notifications;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Notifications\NotificationService;

class MarkAllNotificationsReadAction
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor): int
    {
        $count = $this->notifications->visibleQuery($actor)
            ->whereNull('read_at')
            ->whereNull('archived_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);

        if ($count > 0) {
            $this->audit->record('notification.marked_all_read', $actor, $actor, ['count' => $count], [
                'module' => 'notifications',
                'action' => 'Marked all read',
                'severity' => 'info',
            ]);
        }

        return $count;
    }
}
