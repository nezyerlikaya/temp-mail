<?php

namespace App\Actions\Domains;

use App\Models\Domain;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Domains\DomainStore;

class UpdateDomainAction
{
    public function __construct(
        private readonly DomainStore $store,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, Domain $domain, array $data): Domain
    {
        $wasDefault = $domain->is_default;
        $updated = $this->store->update($domain, $data, $actor);

        $this->audit->record('domain.updated', $actor, null, [
            'domain_id' => $updated->id,
            'domain_name' => $updated->domain_name,
            'status' => $updated->status,
        ], ['module' => 'mail-infrastructure', 'action' => 'Domain updated', 'severity' => 'critical', 'target' => $updated]);

        if (! $wasDefault && $updated->is_default) {
            $this->audit->record('domain.default_changed', $actor, null, [
                'domain_id' => $updated->id,
                'domain_name' => $updated->domain_name,
            ], ['module' => 'mail-infrastructure', 'action' => 'Default domain changed', 'severity' => 'critical', 'target' => $updated]);
        }

        return $updated;
    }
}
