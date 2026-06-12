<?php

namespace App\Actions\Mail;

use App\Models\SmtpConnection;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mail\SmtpSettingsStore;

class SetDefaultSmtpConnectionAction
{
    public function __construct(private readonly SmtpSettingsStore $store, private readonly AuditLogger $audit) {}

    public function handle(User $actor, SmtpConnection $connection): SmtpConnection
    {
        $connection = $this->store->setDefault($connection, $actor);

        $this->audit->record('smtp.default_changed', $actor, null, [
            'smtp_connection_id' => $connection->id,
            'name' => $connection->name,
        ], ['module' => 'mail-infrastructure', 'action' => 'Default SMTP changed', 'target' => $connection]);

        return $connection;
    }
}
