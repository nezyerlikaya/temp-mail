<?php

namespace App\Actions\Mail;

use App\Models\InboundMailConnection;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mail\ImapConnectionTester;
use App\Services\Mail\ImapSettingsStore;
use App\Services\Notifications\NotificationService;

class TestInboundMailConnectionAction
{
    public function __construct(
        private readonly ImapConnectionTester $tester,
        private readonly ImapSettingsStore $store,
        private readonly NotificationService $notifications,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, InboundMailConnection $connection): InboundMailConnection
    {
        $result = $this->tester->test($connection);
        $connection = $this->store->recordTest($connection, $result, $actor);

        if ($connection->status === 'failed') {
            $this->notifications->dispatch([
                'event_key' => 'inbound_mail_connection_failed',
                'type' => 'inbound_mail_health',
                'severity' => 'warning',
                'title' => 'Inbound mail connection needs attention',
                'message' => $connection->name.' failed its latest readiness test. Review the host, port, encryption, credentials, and mailbox folder.',
                'related_module' => 'mail-infrastructure',
                'target_type' => InboundMailConnection::class,
                'target_id' => $connection->id,
                'action_route' => 'admin.imap-smtp.edit',
                'action_parameters' => ['inboundMailConnection' => $connection->id],
            ], sendEmail: false);
        }

        $this->audit->record('inbound_mail.connection_tested', $actor, null, [
            'connection_id' => $connection->id,
            'domain_id' => $connection->domain_id,
            'status' => $connection->status,
            'checks' => collect($result['checks'])->map(fn (array $check): string => $check['status'])->all(),
        ], [
            'module' => 'mail-infrastructure',
            'action' => 'Inbound connection tested',
            'severity' => $connection->status === 'connected' ? 'info' : 'warning',
            'target' => $connection,
        ]);

        return $connection;
    }
}
