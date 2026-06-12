<?php

namespace App\Actions\Domains;

use App\Models\Domain;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Domains\DomainStore;

class SetDefaultDomainAction
{
    public function __construct(private readonly DomainStore $store, private readonly AuditLogger $audit) {}

    public function handle(User $actor, Domain $domain): Domain
    {
        $domain = $this->store->setDefault($domain, $actor);

        $this->audit->record('domain.default_changed', $actor, null, [
            'domain_id' => $domain->id,
            'domain_name' => $domain->domain_name,
        ], ['module' => 'mail-infrastructure', 'action' => 'Default domain changed', 'severity' => 'critical', 'target' => $domain]);

        return $domain;
    }
}
