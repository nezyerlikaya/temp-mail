<?php

namespace App\Actions\Mail;

use App\Models\SmtpConnection;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendSmtpTestEmailAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @return array{status: string, message: string} */
    public function handle(User $actor, SmtpConnection $connection, string $recipient): array
    {
        $mailer = 'smtp_connection_'.$connection->id;
        config([
            'mail.mailers.'.$mailer => [
                'transport' => 'smtp',
                'scheme' => $connection->encryption === 'ssl' ? 'smtps' : 'smtp',
                'host' => $connection->host,
                'port' => $connection->port,
                'username' => $connection->username,
                'password' => (string) $connection->encrypted_password,
                'timeout' => $connection->connection_timeout,
                'auto_tls' => $connection->encryption !== 'none',
                'verify_peer' => $connection->validate_certificate,
            ],
            'mail.from.address' => $connection->from_email,
            'mail.from.name' => $connection->from_name,
        ]);

        try {
            Mail::mailer($mailer)->raw('SMTP transactional delivery readiness test for '.config('app.name').'.', function ($message) use ($connection, $recipient): void {
                $message->to($recipient)->from($connection->from_email, $connection->from_name)->subject('SMTP readiness test');

                if (filled($connection->reply_to_email)) {
                    $message->replyTo($connection->reply_to_email);
                }
            });

            $this->audit->record('smtp.test_email_sent', $actor, null, [
                'smtp_connection_id' => $connection->id,
                'recipient_domain' => str($recipient)->after('@')->toString(),
            ], ['module' => 'mail-infrastructure', 'action' => 'SMTP test email sent', 'target' => $connection]);

            return ['status' => 'sent', 'message' => 'SMTP test email sent.'];
        } catch (Throwable) {
            return ['status' => 'failed', 'message' => 'SMTP test email could not be sent. Check host, sender, recipient, and provider limits.'];
        }
    }
}
