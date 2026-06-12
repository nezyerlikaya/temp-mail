<?php

namespace App\Actions\Mail;

use App\Models\SmtpConnection;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mail\MailHealthNotificationDispatcher;
use App\Services\Mail\SmtpConnectionTester;
use App\Services\Mail\SmtpSettingsStore;

class TestSmtpConnectionAction
{
    public function __construct(
        private readonly SmtpConnectionTester $tester,
        private readonly SmtpSettingsStore $store,
        private readonly MailHealthNotificationDispatcher $notifications,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, SmtpConnection $connection): SmtpConnection
    {
        $result = $this->tester->test($connection);
        $connection = $this->store->recordTest($connection, $result, $actor);

        if ($connection->status === 'failed') {
            $this->notifications->smtpFailed($connection);
        }

        $this->audit->record('smtp.connection_tested', $actor, null, [
            'smtp_connection_id' => $connection->id,
            'status' => $connection->status,
            'checks' => collect($result['checks'])->map(fn (array $check): string => $check['status'])->all(),
        ], [
            'module' => 'mail-infrastructure',
            'action' => 'SMTP connection tested',
            'severity' => $connection->status === 'connected' ? 'info' : 'warning',
            'target' => $connection,
        ]);

        return $connection;
    }
}
