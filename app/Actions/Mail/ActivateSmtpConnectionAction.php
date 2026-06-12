<?php

namespace App\Actions\Mail;

use App\Models\SmtpConnection;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mail\SmtpSettingsStore;

class ActivateSmtpConnectionAction
{
    public function __construct(private readonly SmtpSettingsStore $store, private readonly AuditLogger $audit) {}

    public function handle(User $actor, SmtpConnection $connection): SmtpConnection
    {
        $connection = $this->store->activate($connection, $actor);

        $this->audit->record('smtp.connection_activated', $actor, null, [
            'smtp_connection_id' => $connection->id,
        ], ['module' => 'mail-infrastructure', 'action' => 'SMTP connection activated', 'target' => $connection]);

        return $connection;
    }
}
