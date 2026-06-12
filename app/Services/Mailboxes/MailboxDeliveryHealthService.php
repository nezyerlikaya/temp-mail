<?php

namespace App\Services\Mailboxes;

use App\Models\Domain;
use App\Models\InboundMailConnection;
use App\Models\MailboxDeliveryHealthCheck;
use App\Models\MailboxMessage;

class MailboxDeliveryHealthService
{
    /** @return array<string, mixed> */
    public function summary(): array
    {
        $activeDomains = Domain::query()->where('is_active', true)->count();
        $readyDomains = Domain::query()->where('is_active', true)->where('status', 'ready')->count();
        $activeInbound = InboundMailConnection::query()->where('is_active', true)->count();
        $connectedInbound = InboundMailConnection::query()->where('is_active', true)->where('status', 'connected')->count();
        $recentMessages = MailboxMessage::query()->whereNull('deleted_at')->where('received_at', '>=', now()->subDay())->count();

        $cards = [
            $this->card('Active domains', $activeDomains > 0 ? 'healthy' : 'offline', $activeDomains.' active', 'Receiving domains configured in Domain Management.', 'admin.domains.index'),
            $this->card('DNS readiness', $activeDomains === 0 ? 'offline' : ($readyDomains === $activeDomains ? 'healthy' : 'degraded'), $readyDomains.'/'.$activeDomains.' ready', 'Uses the latest domain DNS status.', 'admin.domains.index'),
            $this->card('Inbound connections', $activeInbound === 0 ? 'offline' : ($connectedInbound === $activeInbound ? 'healthy' : 'degraded'), $connectedInbound.'/'.$activeInbound.' connected', 'Uses existing IMAP connection health.', 'admin.imap-smtp.index'),
            $this->card('Recent receipt', $recentMessages > 0 ? 'healthy' : 'degraded', $recentMessages.' in 24h', 'Receipt activity is a readiness signal, not a real-time monitor.', 'admin.mailbox-operations.index'),
        ];

        $status = collect($cards)->contains(fn (array $card): bool => $card['status'] === 'offline')
            ? 'offline'
            : (collect($cards)->contains(fn (array $card): bool => $card['status'] === 'degraded') ? 'degraded' : 'healthy');

        return ['status' => $status, 'cards' => $cards, 'checked_at' => now()->toIso8601String()];
    }

    public function latest(): ?MailboxDeliveryHealthCheck
    {
        return MailboxDeliveryHealthCheck::query()->latest('checked_at')->first();
    }

    public function history(): mixed
    {
        return MailboxDeliveryHealthCheck::query()->latest('checked_at')->limit(10)->get();
    }

    /** @return array<string, string> */
    private function card(string $label, string $status, string $metric, string $message, string $route): array
    {
        return compact('label', 'status', 'metric', 'message', 'route');
    }
}
