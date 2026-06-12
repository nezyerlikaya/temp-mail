<?php

namespace App\Actions\Domains;

use App\Models\Domain;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Domains\DomainStore;

class CreateDomainAction
{
    public function __construct(
        private readonly DomainStore $store,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, array $data): Domain
    {
        $domain = $this->store->create($data, $actor);

        $this->audit->record('domain.created', $actor, null, [
            'domain_id' => $domain->id,
            'domain_name' => $domain->domain_name,
            'is_default' => $domain->is_default,
        ], ['module' => 'mail-infrastructure', 'action' => 'Domain created', 'severity' => 'critical', 'target' => $domain]);

        if ($domain->is_default) {
            $this->audit->record('domain.default_changed', $actor, null, [
                'domain_id' => $domain->id,
                'domain_name' => $domain->domain_name,
            ], ['module' => 'mail-infrastructure', 'action' => 'Default domain changed', 'severity' => 'critical', 'target' => $domain]);
        }

        return $domain;
    }
}
