<?php

namespace App\Services\Security;

use App\Models\User;
use App\Services\Users\AdminProtectionGuard;
use App\Services\Users\RolePermissionResolver;

class AdminAccessGuard
{
    public function __construct(
        private readonly RolePermissionResolver $permissions,
        private readonly AdminProtectionGuard $protection,
    ) {}

    public function canAccessAdmin(User $user): bool
    {
        return $user->status === 'active' && $this->permissions->canAccessAdmin($user);
    }

    /** @return array<string, mixed> */
    public function readiness(array $adminAccess, array $ipAccess): array
    {
        return [
            'password_policy' => $adminAccess['password_min_length'] >= 8 && $adminAccess['password_letters'] && $adminAccess['password_numbers'] && $adminAccess['password_symbols'] ? 'ready' : 'passive',
            'email_verification' => $adminAccess['require_email_verification'] ? 'ready' : 'passive',
            'session_lifetime' => $adminAccess['admin_session_lifetime'] <= 240 ? 'ready' : 'passive',
            'login_alerts' => $adminAccess['login_alerts'] ? 'ready' : 'passive',
            'admin_ip_allowlist' => count($ipAccess['allowlist']) > 0 || $adminAccess['admin_ip_allowlist_ready'] ? 'ready' : 'passive',
            'two_factor' => $adminAccess['require_2fa_readiness'] ? 'ready' : 'passive',
            'critical_notifications' => $adminAccess['critical_notifications_ready'] ? 'ready' : 'passive',
            'owner_last_admin_protection' => $this->protection->readinessStatus(),
        ];
    }
}
