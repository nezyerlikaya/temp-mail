<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Services\Users\RolePermissionResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class NotificationRecipientResolver
{
    public function __construct(private readonly RolePermissionResolver $permissions) {}

    /** @param array<int, User|int>|null $recipients */
    public function resolve(string $eventKey, string $severity, ?string $module, ?array $recipients = null): Collection
    {
        if ($recipients !== null && $recipients !== []) {
            return User::query()
                ->whereIn('id', collect($recipients)->map(fn (User|int $recipient): int => $recipient instanceof User ? (int) $recipient->getKey() : (int) $recipient)->all())
                ->get()
                ->filter(fn (User $user): bool => $this->canReceive($user, $module))
                ->values();
        }

        $query = User::query()->where('status', 'active');

        if ($severity === 'critical' || $this->isSecurityCritical($eventKey)) {
            $query->whereIn('role', ['owner', 'admin']);
        } else {
            $query->whereIn('role', ['owner', 'admin', 'editor', 'moderator']);
        }

        return $query->get()
            ->filter(fn (User $user): bool => $this->canReceive($user, $module))
            ->values();
    }

    public function canReceive(User $user, ?string $module): bool
    {
        if (! $this->permissions->canAccessAdmin($user)) {
            return false;
        }

        if (Gate::forUser($user)->denies('admin.notifications.view')) {
            return false;
        }

        if ($module === null) {
            return true;
        }

        return collect($this->modulePermissions($module))
            ->contains(fn (string $ability): bool => Gate::forUser($user)->allows($ability));
    }

    /** @return array<int, string> */
    public function modulePermissions(?string $module): array
    {
        return match ($module) {
            'content' => ['admin.comment-moderation.view', 'admin.page-studio.view', 'admin.blog-studio.view', 'admin.sections-studio.view'],
            'trust' => ['admin.security-defense-center.view', 'admin.abuse-reports.view', 'admin.activity-audit-logs.view'],
            'mail-infrastructure' => ['admin.domains.view', 'admin.imap-smtp.view', 'admin.mailbox-operations.view'],
            'system' => ['admin.update-center.view', 'admin.backups-health.view', 'admin.settings.view'],
            'billing' => ['admin.plans-memberships.view'],
            default => ['admin.notifications.view'],
        };
    }

    private function isSecurityCritical(string $eventKey): bool
    {
        return in_array($eventKey, ['failed_admin_login', 'security_setting_changed'], true);
    }
}
