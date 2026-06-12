<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Security\RateLimitPolicyStore;

class UpdateAdminSecurityAction
{
    public function __construct(
        private readonly RateLimitPolicyStore $store,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, array $data): void
    {
        $this->store->putAdminAccess($data, $actor);

        $this->audit->record('security.admin_access_updated', $actor, $actor, [
            'password_min_length' => (int) ($data['password_min_length'] ?? 12),
            'require_email_verification' => (bool) ($data['require_email_verification'] ?? false),
            'admin_session_lifetime' => (int) ($data['admin_session_lifetime'] ?? 120),
            'login_alerts' => (bool) ($data['login_alerts'] ?? false),
            'admin_ip_allowlist_ready' => (bool) ($data['admin_ip_allowlist_ready'] ?? false),
            'require_2fa_readiness' => (bool) ($data['require_2fa_readiness'] ?? false),
        ], [
            'module' => 'security',
            'action' => 'Admin access security updated',
            'severity' => 'critical',
        ]);
    }
}
