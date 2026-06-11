<?php

namespace App\Services\Users;

use App\Enums\UserRole;
use App\Models\User;

class RolePermissionResolver
{
    /**
     * @return array<string, array<string, string>>
     */
    public function abilityMap(): array
    {
        return [
            'Workspace' => [
                'admin.operations.view' => 'Operations Overview',
                'admin.mailbox-operations.view' => 'Mailbox Operations',
                'admin.product-analytics.view' => 'Product Analytics',
            ],
            'Markets' => [
                'admin.locale-launch-center.view' => 'Locale Launch Center',
                'admin.locale-launch-center.manage' => 'Manage locale launch readiness',
                'admin.locale-launch-center.preview' => 'Preview locale readiness',
                'admin.locale-launch-center.publish' => 'Publish or take locale offline',
                'admin.translation-center.view' => 'Translation Center',
            ],
            'Content' => [
                'admin.page-studio.view' => 'Page Studio',
                'admin.blog-studio.view' => 'Blog Studio',
                'admin.taxonomy.view' => 'Taxonomy',
                'admin.sections-studio.view' => 'Sections Studio',
                'admin.media-library.view' => 'Media Library',
                'admin.media-library.picker' => 'View media picker',
                'admin.media-library.select' => 'Select media assets',
                'admin.media-library.upload' => 'Upload media assets',
                'admin.media-library.upload-through-picker' => 'Upload through media picker',
                'admin.media-library.update' => 'Update media metadata',
                'admin.media-library.usage' => 'View media usage',
                'admin.comment-moderation.view' => 'Comment Moderation',
                'admin.seo-growth-center.view' => 'SEO Growth Center',
            ],
            'Mail Infrastructure' => [
                'admin.domains.view' => 'Domains',
                'admin.imap-smtp.view' => 'IMAP/SMTP',
                'admin.mailbox-rules.view' => 'Mailbox Rules',
                'admin.blocked-lists.view' => 'Blocked Lists',
            ],
            'Growth' => [
                'admin.plans-memberships.view' => 'Plans & Memberships',
                'admin.api-access.view' => 'API Access',
                'admin.integrations.view' => 'Integrations',
            ],
            'People' => [
                'admin.people-identity.view' => 'People & Identity',
                'admin.roles-permissions.view' => 'Roles & Permissions',
                'admin.roles-permissions.manage' => 'Assign roles',
                'admin.author-profiles.view' => 'Author Profiles',
            ],
            'Brand' => [
                'admin.theme-launch-center.view' => 'Theme Launch Center',
                'admin.appearance-studio.view' => 'Appearance Studio',
                'admin.typography-center.view' => 'Typography Center',
            ],
            'Trust' => [
                'admin.security-defense-center.view' => 'Security Defense Center',
                'admin.abuse-reports.view' => 'Abuse Reports',
                'admin.activity-audit-logs.view' => 'Activity & Audit Logs',
                'admin.activity-audit-logs.export' => 'Export audit logs',
                'admin.activity-audit-logs.manage-retention' => 'Update audit retention',
            ],
            'System' => [
                'admin.update-center.view' => 'Update Center',
                'admin.update-center.check' => 'Check for updates',
                'admin.update-center.install' => 'Install updates',
                'admin.update-center.manual-upload' => 'Upload manual update package',
                'admin.update-center.rollback' => 'Review rollback readiness',
                'admin.notifications.view' => 'Notifications',
                'admin.email-templates.view' => 'Email Templates',
                'admin.backups-health.view' => 'Backups & Health',
                'admin.backups-health.create' => 'Create backups',
                'admin.backups-health.download' => 'Download backups',
                'admin.backups-health.delete' => 'Delete backups',
                'admin.backups-health.run-health' => 'Run system health checks',
                'admin.settings.view' => 'Settings',
                'admin.settings.manage' => 'Update global settings',
            ],
        ];
    }

    public function allows(User $user, string $ability): bool
    {
        $role = $this->roleFor($user);

        if (in_array($ability, $this->ownerOnlyAbilities(), true)) {
            return $role === UserRole::Owner;
        }

        if (in_array($role, [UserRole::Owner, UserRole::Admin], true)) {
            return str_starts_with($ability, 'admin.');
        }

        return in_array($ability, $this->grants()[$role->value] ?? [], true);
    }

    public function canAccessAdmin(User $user): bool
    {
        return $user->status === 'active' && $this->roleFor($user)->hasAdminAccess();
    }

    public function roleFor(User $user): UserRole
    {
        return UserRole::tryFrom((string) $user->role) ?? UserRole::Member;
    }

    /** @return array<string, string> */
    public function roleOptions(): array
    {
        return collect(UserRole::cases())->mapWithKeys(fn (UserRole $role): array => [
            $role->value => $role->label(),
        ])->all();
    }

    /** @return array<int, array{role: UserRole, count: int, permissions: int}> */
    public function roleSummaries(): array
    {
        $abilities = collect($this->abilityMap())
            ->flatMap(fn (array $group): array => array_keys($group));

        return collect(UserRole::cases())->map(fn (UserRole $role): array => [
            'role' => $role,
            'count' => User::query()->where('role', $role->value)->count(),
            'permissions' => $abilities
                ->filter(fn (string $ability): bool => $this->allowsRole($role, $ability))
                ->count(),
        ])->all();
    }

    /** @return array<int, array{group: string, label: string, ability: string, grants: array<string, bool>}> */
    public function permissionMatrix(): array
    {
        return collect($this->abilityMap())->flatMap(function (array $abilities, string $group): array {
            return collect($abilities)->map(function (string $label, string $ability) use ($group): array {
                return [
                    'group' => $group,
                    'label' => $label,
                    'ability' => $ability,
                    'grants' => collect(UserRole::cases())->mapWithKeys(fn (UserRole $role): array => [
                        $role->value => $this->allowsRole($role, $ability),
                    ])->all(),
                ];
            })->all();
        })->values()->all();
    }

    /** @return array<string, array<int, string>> */
    private function grants(): array
    {
        return [
            UserRole::Editor->value => [
                'admin.operations.view', 'admin.product-analytics.view',
                'admin.locale-launch-center.view', 'admin.translation-center.view',
                'admin.page-studio.view', 'admin.blog-studio.view', 'admin.taxonomy.view',
                'admin.sections-studio.view', 'admin.media-library.view', 'admin.media-library.picker',
                'admin.media-library.select', 'admin.media-library.usage', 'admin.seo-growth-center.view',
                'admin.author-profiles.view', 'admin.notifications.view',
            ],
            UserRole::Moderator->value => [
                'admin.operations.view', 'admin.mailbox-operations.view',
                'admin.comment-moderation.view', 'admin.blocked-lists.view',
                'admin.people-identity.view', 'admin.abuse-reports.view',
                'admin.activity-audit-logs.view',
            ],
            UserRole::Author->value => [
                'admin.operations.view', 'admin.blog-studio.view',
                'admin.media-library.view', 'admin.media-library.picker', 'admin.media-library.select',
                'admin.author-profiles.view',
            ],
            UserRole::Member->value => [],
        ];
    }

    private function allowsRole(UserRole $role, string $ability): bool
    {
        if (in_array($ability, $this->ownerOnlyAbilities(), true)) {
            return $role === UserRole::Owner;
        }

        if (in_array($role, [UserRole::Owner, UserRole::Admin], true)) {
            return str_starts_with($ability, 'admin.');
        }

        return in_array($ability, $this->grants()[$role->value] ?? [], true);
    }

    /** @return array<int, string> */
    private function ownerOnlyAbilities(): array
    {
        return [
            'admin.backups-health.download',
            'admin.backups-health.delete',
        ];
    }
}
