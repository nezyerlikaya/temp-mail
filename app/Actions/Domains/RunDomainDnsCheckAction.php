<?php

namespace App\Actions\Domains;

use App\Models\Domain;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Domains\DomainDnsCheckService;
use App\Services\Domains\DomainHealthNotificationService;
use App\Services\Domains\DomainStatusResolver;
use App\Services\Domains\DomainStore;

class RunDomainDnsCheckAction
{
    public function __construct(
        private readonly DomainDnsCheckService $dns,
        private readonly DomainStatusResolver $status,
        private readonly DomainStore $store,
        private readonly DomainHealthNotificationService $notifications,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, Domain $domain): Domain
    {
        $checks = $this->dns->check($domain);
        $status = $this->status->resolve($checks, $domain->is_active);
        $domain = $this->store->saveDnsCheck($domain, $checks, $status, $actor);

        $this->notifications->dispatchIfNeeded($domain);

        $this->audit->record('domain.dns_check_executed', $actor, null, [
            'domain_id' => $domain->id,
            'domain_name' => $domain->domain_name,
            'status' => $domain->status,
            'ready_records' => collect($checks)->where('status', 'ready')->count(),
        ], ['module' => 'mail-infrastructure', 'action' => 'DNS check executed', 'severity' => $domain->status === 'ready' ? 'info' : 'warning', 'target' => $domain]);

        return $domain;
    }
}
