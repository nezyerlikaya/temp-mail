<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Security\RateLimitPolicyStore;

class UpdateIpAccessAction
{
    public function __construct(
        private readonly RateLimitPolicyStore $store,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, array $data): void
    {
        $this->store->putIpAccess($data, $actor);

        $this->audit->record('security.ip_access_updated', $actor, $actor, [
            'allowlist_count' => count($data['allowlist'] ?? []),
            'blocklist_count' => count($data['blocklist'] ?? []),
            'temporary_block_ready' => (bool) ($data['temporary_block_ready'] ?? false),
        ], [
            'module' => 'security',
            'action' => 'IP access updated',
            'severity' => 'critical',
        ]);
    }
}
