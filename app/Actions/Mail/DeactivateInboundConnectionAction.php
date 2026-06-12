<?php

namespace App\Actions\Mail;

use App\Models\InboundMailConnection;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mail\ImapSettingsStore;

class DeactivateInboundConnectionAction
{
    public function __construct(
        private readonly ImapSettingsStore $store,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, InboundMailConnection $connection): InboundMailConnection
    {
        $connection = $this->store->deactivate($connection, $actor);

        $this->audit->record('inbound_mail.connection_deactivated', $actor, null, [
            'connection_id' => $connection->id,
            'domain_id' => $connection->domain_id,
        ], ['module' => 'mail-infrastructure', 'action' => 'Inbound connection deactivated', 'target' => $connection]);

        return $connection;
    }
}
