<?php

namespace App\Actions\Mail;

use App\Models\SmtpConnection;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mail\SmtpSettingsStore;

class UpdateSmtpConnectionAction
{
    public function __construct(private readonly SmtpSettingsStore $store, private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $data */
    public function create(User $actor, array $data): SmtpConnection
    {
        $connection = $this->store->create($data, $actor);

        $this->audit->record('smtp.connection_created', $actor, null, $this->metadata($connection, filled($data['password'] ?? null)), [
            'module' => 'mail-infrastructure',
            'action' => 'SMTP connection created',
            'target' => $connection,
        ]);

        return $connection;
    }

    /** @param array<string, mixed> $data */
    public function update(User $actor, SmtpConnection $connection, array $data): SmtpConnection
    {
        $connection = $this->store->update($connection, $data, $actor);

        $this->audit->record('smtp.connection_updated', $actor, null, $this->metadata($connection, filled($data['password'] ?? null)), [
            'module' => 'mail-infrastructure',
            'action' => 'SMTP connection updated',
            'target' => $connection,
        ]);

        return $connection;
    }

    /** @return array<string, mixed> */
    private function metadata(SmtpConnection $connection, bool $secretReplaced): array
    {
        return [
            'smtp_connection_id' => $connection->id,
            'domain_id' => $connection->domain_id,
            'name' => $connection->name,
            'host' => $connection->host,
            'port' => $connection->port,
            'encryption' => $connection->encryption,
            'from_domain' => str($connection->from_email)->after('@')->toString(),
            'reply_to_ready' => $connection->reply_to_ready,
            'secret_replaced' => $secretReplaced,
        ];
    }
}
