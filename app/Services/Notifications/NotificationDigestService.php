<?php

namespace App\Services\Notifications;

use App\Models\NotificationRule;
use App\Models\SystemNotification;

class NotificationDigestService
{
    public function shouldDigest(NotificationRule $rule): bool
    {
        return $rule->severity !== 'critical' && $rule->digest_mode === 'daily';
    }

    public function markPending(SystemNotification $notification): SystemNotification
    {
        $notification->forceFill([
            'digest_status' => 'pending',
            'digest_pending_at' => now(),
        ])->save();

        return $notification;
    }

    /** @return array{pending: int, ready: bool, message: string} */
    public function readiness(): array
    {
        $pending = SystemNotification::query()->where('digest_status', 'pending')->count();

        return [
            'pending' => $pending,
            'ready' => true,
            'message' => $pending === 1 ? '1 notification is waiting for the daily digest.' : $pending.' notifications are waiting for the daily digest.',
        ];
    }
}
