<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AdminNavigationRegistry
{
    /**
     * @return array<int, array{label: string, items: array<int, array{label: string, route: string, path: string, icon: string, permission: string, badge: string|null}>}>
     */
    public function groups(): array
    {
        return [
            $this->group('Workspace', [
                $this->item('Operations Overview', 'dashboard', '', 'layout-dashboard', 'admin.operations.view'),
                $this->item('Mailbox Operations', 'admin.mailbox-operations.index', 'mailbox-operations', 'inbox', 'admin.mailbox-operations.view', 'Ready'),
                $this->item('Product Analytics', 'admin.product-analytics.index', 'product-analytics', 'chart-no-axes-combined', 'admin.product-analytics.view'),
            ]),
            $this->group('Markets', [
                $this->item('Locale Launch Center', 'admin.locale-launch-center.index', 'locale-launch-center', 'languages', 'admin.locale-launch-center.view'),
                $this->item('Translation Center', 'admin.translation-center.index', 'translation-center', 'spell-check-2', 'admin.translation-center.view'),
            ]),
            $this->group('Content', [
                $this->item('Page Studio', 'admin.page-studio.index', 'page-studio', 'files', 'admin.page-studio.view'),
                $this->item('Blog Studio', 'admin.blog-studio.index', 'blog-studio', 'notebook-pen', 'admin.blog-studio.view'),
                $this->item('Taxonomy', 'admin.taxonomy.index', 'taxonomy', 'tags', 'admin.taxonomy.view'),
                $this->item('Sections Studio', 'admin.sections-studio.index', 'sections-studio', 'panels-top-left', 'admin.sections-studio.view'),
                $this->item('Media Library', 'admin.media-library.index', 'media-library', 'images', 'admin.media-library.view'),
                $this->item('Comment Moderation', 'admin.comment-moderation.index', 'comment-moderation', 'messages-square', 'admin.comment-moderation.view'),
                $this->item('SEO Growth Center', 'admin.seo-growth-center.index', 'seo-growth-center', 'search-check', 'admin.seo-growth-center.view'),
            ]),
            $this->group('Mail Infrastructure', [
                $this->item('Domains', 'admin.domains.index', 'domains', 'globe-2', 'admin.domains.view'),
                $this->item('IMAP/SMTP', 'admin.imap-smtp.index', 'imap-smtp', 'server-cog', 'admin.imap-smtp.view'),
                $this->item('Mailbox Rules', 'admin.mailbox-rules.index', 'mailbox-rules', 'list-filter', 'admin.mailbox-rules.view'),
                $this->item('Blocked Lists', 'admin.blocked-lists.index', 'blocked-lists', 'shield-ban', 'admin.blocked-lists.view'),
            ]),
            $this->group('Growth', [
                $this->item('Plans & Memberships', 'admin.plans-memberships.index', 'plans-memberships', 'badge-dollar-sign', 'admin.plans-memberships.view'),
                $this->item('API Access', 'admin.api-access.index', 'api-access', 'braces', 'admin.api-access.view'),
                $this->item('Integrations', 'admin.integrations.index', 'integrations', 'plug-zap', 'admin.integrations.view'),
            ]),
            $this->group('People', [
                $this->item('People & Identity', 'admin.people-identity.index', 'people-identity', 'users', 'admin.people-identity.view'),
                $this->item('Roles & Permissions', 'admin.roles-permissions.index', 'roles-permissions', 'key-round', 'admin.roles-permissions.view'),
                $this->item('Author Profiles', 'admin.author-profiles.index', 'author-profiles', 'user-round-pen', 'admin.author-profiles.view'),
            ]),
            $this->group('Brand', [
                $this->item('Theme Launch Center', 'admin.theme-launch-center.index', 'theme-launch-center', 'palette', 'admin.theme-launch-center.view'),
                $this->item('Appearance Studio', 'admin.appearance-studio.index', 'appearance-studio', 'swatch-book', 'admin.appearance-studio.view'),
                $this->item('Typography Center', 'admin.typography-center.index', 'typography-center', 'type', 'admin.typography-center.view'),
            ]),
            $this->group('Trust', [
                $this->item('Security Defense Center', 'admin.security-defense-center.index', 'security-defense-center', 'shield-check', 'admin.security-defense-center.view'),
                $this->item('Abuse Reports', 'admin.abuse-reports.index', 'abuse-reports', 'siren', 'admin.abuse-reports.view'),
                $this->item('Activity & Audit Logs', 'admin.activity-audit-logs.index', 'activity-audit-logs', 'scroll-text', 'admin.activity-audit-logs.view'),
            ]),
            $this->group('System', [
                $this->item('Update Center', 'admin.update-center.index', 'update-center', 'refresh-cw', 'admin.update-center.view'),
                $this->item('Notifications', 'admin.notifications.index', 'notifications', 'bell', 'admin.notifications.view'),
                $this->item('Email Templates', 'admin.email-templates.index', 'email-templates', 'mail', 'admin.email-templates.view'),
                $this->item('Backups & Health', 'admin.backups-health.index', 'backups-health', 'database-backup', 'admin.backups-health.view'),
                $this->item('Settings', 'admin.settings.index', 'settings', 'settings-2', 'admin.settings.view'),
            ]),
        ];
    }

    /**
     * @return array<int, array{label: string, items: array<int, array<string, mixed>>}>
     */
    public function visibleFor(User $user, ?string $currentRoute): array
    {
        return collect($this->groups())
            ->map(function (array $group) use ($user, $currentRoute): array {
                $group['items'] = collect($group['items'])
                    ->filter(fn (array $item): bool => Gate::forUser($user)->allows($item['permission']))
                    ->map(function (array $item) use ($currentRoute): array {
                        $item['active'] = $currentRoute === $item['route'];

                        return $item;
                    })
                    ->values()
                    ->all();

                return $group;
            })
            ->filter(fn (array $group): bool => $group['items'] !== [])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByRoute(?string $routeName): ?array
    {
        if ($routeName === null) {
            return null;
        }

        return collect($this->groups())
            ->flatMap(fn (array $group): array => $group['items'])
            ->firstWhere('route', $routeName);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{label: string, items: array<int, array<string, mixed>>}
     */
    private function group(string $label, array $items): array
    {
        return compact('label', 'items');
    }

    /**
     * @return array{label: string, route: string, path: string, icon: string, permission: string, badge: string|null}
     */
    private function item(
        string $label,
        string $route,
        string $path,
        string $icon,
        string $permission,
        ?string $badge = null,
    ): array {
        return compact('label', 'route', 'path', 'icon', 'permission', 'badge');
    }
}
