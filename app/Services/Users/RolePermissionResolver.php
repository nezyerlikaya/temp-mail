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
                'admin.page-studio.create' => 'Create pages',
                'admin.page-studio.update' => 'Update pages',
                'admin.page-studio.publish' => 'Publish pages',
                'admin.page-studio.hide' => 'Hide pages',
                'admin.page-studio.preview' => 'Preview pages',
                'admin.page-studio.restore' => 'Restore pages',
                'admin.page-studio.delete' => 'Permanently delete pages',
                'admin.page-studio.trash' => 'Trash or restore page readiness',
                'admin.blog-studio.view' => 'Blog Studio',
                'admin.blog-studio.create' => 'Create blog posts',
                'admin.blog-studio.update' => 'Update blog posts',
                'admin.blog-studio.publish' => 'Publish blog posts',
                'admin.blog-studio.hide' => 'Hide blog posts',
                'admin.blog-studio.preview' => 'Preview blog posts',
                'admin.blog-studio.restore' => 'Restore blog posts',
                'admin.blog-studio.trash' => 'Trash blog post readiness',
                'admin.blog-studio.delete' => 'Permanently delete blog posts',
                'admin.taxonomy.view' => 'Taxonomy',
                'admin.taxonomy.create' => 'Create taxonomy records',
                'admin.taxonomy.update' => 'Update taxonomy records',
                'admin.taxonomy.attach' => 'Attach taxonomy to posts',
                'admin.sections-studio.view' => 'Sections Studio',
                'admin.sections-studio.create' => 'Create sections',
                'admin.sections-studio.update' => 'Update sections',
                'admin.sections-studio.reorder' => 'Reorder sections',
                'admin.sections-studio.items.update' => 'Update section items',
                'admin.sections-studio.publish' => 'Publish section readiness',
                'admin.sections-studio.activate' => 'Activate sections',
                'admin.sections-studio.hide' => 'Hide sections',
                'admin.sections-studio.preview' => 'Preview sections',
                'admin.sections-studio.restore' => 'Restore sections',
                'admin.sections-studio.trash' => 'Trash or restore section readiness',
                'admin.sections-studio.delete' => 'Permanently delete sections',
                'admin.media-library.view' => 'Media Library',
                'admin.media-library.picker' => 'View media picker',
                'admin.media-library.select' => 'Select media assets',
                'admin.media-library.upload' => 'Upload media assets',
                'admin.media-library.upload-through-picker' => 'Upload through media picker',
                'admin.media-library.update' => 'Update media metadata',
                'admin.media-library.update-metadata' => 'Update media metadata',
                'admin.media-library.trash' => 'Trash media assets',
                'admin.media-library.restore' => 'Restore media assets',
                'admin.media-library.delete' => 'Permanently delete media assets',
                'admin.media-library.usage' => 'View media usage',
                'admin.comment-moderation.view' => 'Comment Moderation',
                'admin.seo-growth-center.view' => 'SEO Growth Center',
                'admin.seo-growth-center.update' => 'Update SEO records',
                'admin.seo-growth-center.manage' => 'Manage SEO settings',
                'admin.seo-growth-center.preview' => 'Preview SEO records',
                'admin.seo-growth-center.schema' => 'Update SEO schema',
                'admin.seo-growth-center.media' => 'Select SEO media',
                'admin.seo-growth-center.diagnostics' => 'Run SEO diagnostics',
                'admin.seo-growth-center.templates' => 'Manage SEO templates',
                'admin.seo-growth-center.redirects' => 'Manage SEO redirects',
                'admin.seo-growth-center.readiness' => 'Manage sitemap and robots readiness',
                'admin.seo-growth-center.rollback' => 'Rollback SEO versions',
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
                'admin.page-studio.view', 'admin.page-studio.create', 'admin.page-studio.update',
                'admin.page-studio.publish', 'admin.page-studio.hide', 'admin.page-studio.preview',
                'admin.page-studio.restore', 'admin.page-studio.trash',
                'admin.blog-studio.view', 'admin.blog-studio.create', 'admin.blog-studio.update',
                'admin.blog-studio.publish', 'admin.blog-studio.hide', 'admin.blog-studio.preview',
                'admin.blog-studio.restore', 'admin.blog-studio.trash', 'admin.taxonomy.view',
                'admin.taxonomy.create', 'admin.taxonomy.update', 'admin.taxonomy.attach',
                'admin.sections-studio.view', 'admin.sections-studio.create', 'admin.sections-studio.update',
                'admin.sections-studio.reorder', 'admin.sections-studio.items.update',
                'admin.sections-studio.publish', 'admin.sections-studio.activate', 'admin.sections-studio.hide',
                'admin.sections-studio.preview', 'admin.sections-studio.restore', 'admin.sections-studio.trash',
                'admin.media-library.view', 'admin.media-library.picker',
                'admin.media-library.select', 'admin.media-library.usage',
                'admin.seo-growth-center.view', 'admin.seo-growth-center.update',
                'admin.seo-growth-center.preview', 'admin.seo-growth-center.schema',
                'admin.seo-growth-center.media',
                'admin.seo-growth-center.diagnostics', 'admin.seo-growth-center.templates',
                'admin.seo-growth-center.redirects', 'admin.seo-growth-center.readiness',
                'admin.author-profiles.view', 'admin.notifications.view',
            ],
            UserRole::Moderator->value => [
                'admin.operations.view', 'admin.mailbox-operations.view',
                'admin.comment-moderation.view', 'admin.blocked-lists.view',
                'admin.people-identity.view', 'admin.abuse-reports.view',
                'admin.activity-audit-logs.view',
            ],
            UserRole::Author->value => [
                'admin.operations.view', 'admin.blog-studio.view', 'admin.blog-studio.create',
                'admin.blog-studio.update', 'admin.blog-studio.preview', 'admin.taxonomy.view', 'admin.taxonomy.attach', 'admin.page-studio.view',
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
