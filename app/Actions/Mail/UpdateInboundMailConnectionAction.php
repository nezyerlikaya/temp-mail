<?php

namespace App\Actions\Mail;

use App\Models\InboundMailConnection;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mail\ImapSettingsStore;

class UpdateInboundMailConnectionAction
{
    public function __construct(
        private readonly ImapSettingsStore $store,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(User $actor, array $data): InboundMailConnection
    {
        $connection = $this->store->create($data, $actor);

        $this->audit->record('inbound_mail.connection_created', $actor, null, $this->metadata($connection, filled($data['password'] ?? null)), [
            'module' => 'mail-infrastructure',
            'action' => 'Inbound connection created',
            'target' => $connection,
        ]);

        return $connection;
    }

    /** @param array<string, mixed> $data */
    public function update(User $actor, InboundMailConnection $connection, array $data): InboundMailConnection
    {
        $connection = $this->store->update($connection, $data, $actor);

        $this->audit->record('inbound_mail.connection_updated', $actor, null, $this->metadata($connection, filled($data['password'] ?? null)), [
            'module' => 'mail-infrastructure',
            'action' => 'Inbound connection updated',
            'target' => $connection,
        ]);

        return $connection;
    }

    /** @return array<string, mixed> */
    private function metadata(InboundMailConnection $connection, bool $secretReplaced): array
    {
        return [
            'connection_id' => $connection->id,
            'domain_id' => $connection->domain_id,
            'name' => $connection->name,
            'host' => $connection->host,
            'port' => $connection->port,
            'encryption' => $connection->encryption,
            'mailbox' => $connection->mailbox,
            'certificate_validation' => $connection->validate_certificate,
            'secret_replaced' => $secretReplaced,
        ];
    }
}
