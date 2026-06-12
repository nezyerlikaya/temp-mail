<?php

namespace App\Services\Mailboxes;

use App\Services\Notifications\NotificationService;

class MailboxHealthNotificationDispatcher
{
    public function __construct(private readonly NotificationService $notifications) {}

    /** @param array<string, mixed> $summary */
    public function dispatch(array $summary): void
    {
        if ($summary['status'] === 'healthy') {
            return;
        }

        $this->notifications->dispatch([
            'event_key' => 'mailbox_delivery_health_degraded',
            'type' => 'mailbox_delivery_health',
            'severity' => $summary['status'] === 'offline' ? 'critical' : 'warning',
            'title' => 'Mailbox delivery health needs review',
            'message' => 'Mailbox delivery is '.$summary['status'].'. Review domain DNS and inbound connection readiness.',
            'related_module' => 'mail-infrastructure',
            'action_route' => 'admin.mailbox-rules.index',
        ], sendEmail: false);
    }
}
