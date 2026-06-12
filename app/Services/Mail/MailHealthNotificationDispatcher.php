<?php

namespace App\Services\Mail;

use App\Models\SmtpConnection;
use App\Services\Notifications\NotificationService;

class MailHealthNotificationDispatcher
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function smtpFailed(SmtpConnection $connection): void
    {
        $this->notifications->dispatch([
            'event_key' => 'smtp_connection_failed',
            'type' => 'smtp_health',
            'severity' => 'warning',
            'title' => 'SMTP connection needs attention',
            'message' => $connection->name.' failed its latest transactional delivery readiness test.',
            'related_module' => 'mail-infrastructure',
            'target_type' => SmtpConnection::class,
            'target_id' => $connection->id,
            'action_route' => 'admin.imap-smtp.smtp.edit',
            'action_parameters' => ['smtpConnection' => $connection->id],
        ], sendEmail: false);
    }

    /** @param array{overall: string, failed: int, warning: int} $summary */
    public function infrastructureDegraded(array $summary): void
    {
        if ($summary['overall'] === 'healthy') {
            return;
        }

        $this->notifications->dispatch([
            'event_key' => 'mail_infrastructure_degraded',
            'type' => 'mail_infrastructure_health',
            'severity' => $summary['failed'] > 0 ? 'critical' : 'warning',
            'title' => 'Mail infrastructure health needs review',
            'message' => 'Mail infrastructure checks found '.$summary['failed'].' failed and '.$summary['warning'].' warning item(s).',
            'related_module' => 'mail-infrastructure',
            'action_route' => 'admin.imap-smtp.index',
        ], sendEmail: false);
    }
}
