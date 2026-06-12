<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Security\RateLimitPolicyStore;

class UpdateRateLimitsAction
{
    public function __construct(
        private readonly RateLimitPolicyStore $store,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, array $data): void
    {
        $this->store->putPolicies($data['policies'] ?? [], $actor);

        $this->audit->record('security.rate_limits_updated', $actor, $actor, [
            'actions' => array_keys($data['policies'] ?? []),
        ], [
            'module' => 'security',
            'action' => 'Rate limits updated',
            'severity' => 'critical',
        ]);
    }
}
