<?php

namespace App\Services\Mail;

use App\Models\Domain;
use App\Models\InboundMailConnection;
use App\Models\SmtpConnection;

class MailInfrastructureHealthService
{
    public function __construct(private readonly InboundMailExtensionChecker $imapExtension) {}

    /** @return array{overall: string, failed: int, warning: int, healthy: int, cards: array<int, array<string, mixed>>, generated_at: string} */
    public function summary(): array
    {
        $cards = [
            $this->domainHealth(),
            $this->inboundHealth(),
            $this->smtpHealth(),
            $this->extensionHealth(),
        ];

        $failed = collect($cards)->where('status', 'failed')->count();
        $warning = collect($cards)->where('status', 'warning')->count();
        $healthy = collect($cards)->where('status', 'healthy')->count();

        return [
            'overall' => $failed > 0 ? 'failed' : ($warning > 0 ? 'warning' : 'healthy'),
            'failed' => $failed,
            'warning' => $warning,
            'healthy' => $healthy,
            'cards' => $cards,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function domainHealth(): array
    {
        $total = Domain::query()->count();
        $ready = Domain::query()->where('status', 'ready')->count();
        $failed = Domain::query()->whereIn('status', ['degraded', 'offline'])->count();

        return [
            'label' => 'Domain DNS health',
            'status' => $failed > 0 ? 'failed' : ($total === 0 || $ready < $total ? 'warning' : 'healthy'),
            'metric' => $ready.'/'.$total.' ready',
            'message' => $total === 0 ? 'Create a receiving domain before publishing mail flows.' : 'DNS readiness is based on the latest domain checks.',
            'route' => 'admin.domains.index',
        ];
    }

    /** @return array<string, mixed> */
    private function inboundHealth(): array
    {
        $total = InboundMailConnection::query()->count();
        $connected = InboundMailConnection::query()->where('status', 'connected')->count();
        $failed = InboundMailConnection::query()->where('status', 'failed')->count();

        return [
            'label' => 'Inbound IMAP health',
            'status' => $failed > 0 ? 'failed' : ($total === 0 || $connected < $total ? 'warning' : 'healthy'),
            'metric' => $connected.'/'.$total.' connected',
            'message' => $total === 0 ? 'Inbound connection setup is still empty.' : 'IMAP readiness uses read-only connection checks.',
            'route' => 'admin.imap-smtp.index',
        ];
    }

    /** @return array<string, mixed> */
    private function smtpHealth(): array
    {
        $total = SmtpConnection::query()->count();
        $connected = SmtpConnection::query()->where('status', 'connected')->count();
        $failed = SmtpConnection::query()->where('status', 'failed')->count();

        return [
            'label' => 'Outbound SMTP health',
            'status' => $failed > 0 ? 'failed' : ($total === 0 || $connected < $total ? 'warning' : 'healthy'),
            'metric' => $connected.'/'.$total.' connected',
            'message' => $total === 0 ? 'Add SMTP for password resets, notifications, and system delivery.' : 'SMTP readiness excludes newsletter or marketing delivery.',
            'route' => 'admin.imap-smtp.index',
        ];
    }

    /** @return array<string, mixed> */
    private function extensionHealth(): array
    {
        $extension = $this->imapExtension->check();

        return [
            'label' => 'Runtime readiness',
            'status' => $extension['ready'] ? 'healthy' : 'warning',
            'metric' => $extension['ready'] ? 'IMAP ready' : 'IMAP missing',
            'message' => $extension['message'],
            'route' => 'admin.imap-smtp.index',
        ];
    }
}
