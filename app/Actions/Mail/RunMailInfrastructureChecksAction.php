<?php

namespace App\Actions\Mail;

use App\Actions\Domains\RunDomainDnsCheckAction;
use App\Models\Domain;
use App\Models\InboundMailConnection;
use App\Models\SmtpConnection;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mail\MailHealthNotificationDispatcher;
use App\Services\Mail\MailInfrastructureHealthService;

class RunMailInfrastructureChecksAction
{
    public function __construct(
        private readonly RunDomainDnsCheckAction $domainCheck,
        private readonly TestInboundMailConnectionAction $inboundCheck,
        private readonly TestSmtpConnectionAction $smtpCheck,
        private readonly MailInfrastructureHealthService $health,
        private readonly MailHealthNotificationDispatcher $notifications,
        private readonly AuditLogger $audit,
    ) {}

    /** @return array<string, mixed> */
    public function handle(User $actor): array
    {
        Domain::query()->where('is_active', true)->get()
            ->each(fn (Domain $domain) => $this->domainCheck->handle($actor, $domain));

        InboundMailConnection::query()->where('is_active', true)->get()
            ->each(fn (InboundMailConnection $connection) => $this->inboundCheck->handle($actor, $connection));

        SmtpConnection::query()->where('is_active', true)->get()
            ->each(fn (SmtpConnection $connection) => $this->smtpCheck->handle($actor, $connection));

        $summary = $this->health->summary();
        $this->notifications->infrastructureDegraded($summary);

        $this->audit->record('mail_infrastructure.checks_run', $actor, null, [
            'overall' => $summary['overall'],
            'failed' => $summary['failed'],
            'warning' => $summary['warning'],
        ], ['module' => 'mail-infrastructure', 'action' => 'Mail infrastructure checks run', 'severity' => $summary['overall'] === 'healthy' ? 'info' : 'warning']);

        return $summary;
    }
}
