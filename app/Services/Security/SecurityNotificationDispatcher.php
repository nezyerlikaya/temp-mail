<?php

namespace App\Services\Security;

use App\Models\AbuseSignal;
use App\Services\Notifications\NotificationService;

class SecurityNotificationDispatcher
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function dispatchCritical(AbuseSignal $signal): void
    {
        if ($signal->severity !== 'critical') {
            return;
        }

        $this->notifications->dispatch([
            'event_key' => 'critical_security_signal',
            'type' => 'security_alert',
            'severity' => 'critical',
            'title' => 'Critical security signal',
            'message' => str($signal->signal_type)->replace('_', ' ')->headline()->append(' requires review.')->toString(),
            'related_module' => 'trust',
            'target_type' => AbuseSignal::class,
            'target_id' => $signal->id,
            'action_route' => 'admin.security-defense-center.index',
            'action_parameters' => ['status' => 'open', 'severity' => 'critical'],
        ], sendEmail: false);
    }
}
