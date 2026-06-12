<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AdminCommandRegistry
{
    public function __construct(private readonly AdminNavigationRegistry $navigation) {}

    /**
     * @return array<int, array{id: string, title: string, description: string, group: string, route: string, keywords: array<int, string>, permission: string, danger: bool, icon: string}>
     */
    public function commands(): array
    {
        $navigationCommands = collect($this->navigation->groups())
            ->flatMap(function (array $group): array {
                return collect($group['items'])
                    ->filter(fn (array $item): bool => in_array($item['label'], $this->initialModuleLabels(), true))
                    ->map(fn (array $item): array => $this->command(
                        title: 'Go to '.$item['label'],
                        description: 'Open the '.$item['label'].' workspace.',
                        group: $group['label'],
                        route: $item['route'],
                        keywords: $this->keywordsFor($item['label']),
                        permission: $item['permission'],
                        icon: $item['icon'],
                    ))
                    ->all();
            })
            ->all();

        return [
            ...$navigationCommands,
            $this->command(
                title: 'Create blog post',
                description: 'Open Blog Studio to start a new publishing workflow.',
                group: 'Create',
                route: 'admin.blog-studio.index',
                keywords: ['blog', 'post', 'article', 'write', 'publish'],
                permission: 'admin.blog-studio.view',
                icon: 'file-plus-2',
            ),
            $this->command(
                title: 'Create page',
                description: 'Open Page Studio to start a new page workflow.',
                group: 'Create',
                route: 'admin.page-studio.index',
                keywords: ['page', 'content', 'legal', 'publish'],
                permission: 'admin.page-studio.view',
                icon: 'file-plus',
            ),
            $this->command(
                title: 'Check for updates',
                description: 'Review available application updates before taking action.',
                group: 'System actions',
                route: 'admin.update-center.index',
                keywords: ['update', 'upgrade', 'version', 'release'],
                permission: 'admin.update-center.view',
                icon: 'refresh-cw',
                danger: true,
            ),
            $this->command(
                title: 'Create backup',
                description: 'Open Backups & Health to review and create a backup safely.',
                group: 'System actions',
                route: 'admin.backups-health.index',
                keywords: ['backup', 'restore', 'health', 'database'],
                permission: 'admin.backups-health.view',
                icon: 'database-backup',
                danger: true,
            ),
        ];
    }

    /**
     * @return array<int, array{id: string, title: string, description: string, group: string, route: string, url: string, keywords: array<int, string>, permission: string, danger: bool, icon: string}>
     */
    public function visibleFor(User $user): array
    {
        return collect($this->commands())
            ->filter(fn (array $command): bool => Gate::forUser($user)->allows($command['permission']))
            ->map(function (array $command): array {
                $command['url'] = route($command['route']);

                return $command;
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function initialModuleLabels(): array
    {
        return [
            'Operations Overview',
            'Mailbox Operations',
            'Product Analytics',
            'Locale Launch Center',
            'Translation Center',
            'Blog Studio',
            'Page Studio',
            'Sections Studio',
            'Media Library',
            'SEO Growth Center',
            'Security Defense Center',
            'People & Identity',
            'Roles & Permissions',
            'Activity & Audit Logs',
            'Theme Launch Center',
            'Typography Center',
            'Update Center',
            'Email Templates',
            'Backups & Health',
            'Settings',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function keywordsFor(string $label): array
    {
        return match ($label) {
            'Locale Launch Center' => ['language', 'locale', 'market', 'region', 'translation'],
            'SEO Growth Center' => ['seo', 'search', 'metadata', 'sitemap'],
            'Backups & Health' => ['backup', 'restore', 'health', 'database'],
            'Email Templates' => ['email', 'mail', 'template', 'notification', 'system'],
            'Mailbox Operations' => ['mail', 'inbox', 'message', 'mailbox'],
            'Security Defense Center' => ['security', 'defense', 'captcha', 'abuse'],
            'Theme Launch Center' => ['theme', 'design', 'horizon', 'atlas', 'legacy'],
            'Typography Center' => ['font', 'typography', 'typeface'],
            'People & Identity' => ['user', 'people', 'identity', 'account'],
            'Roles & Permissions' => ['role', 'permission', 'access', 'admin'],
            'Activity & Audit Logs' => ['audit', 'activity', 'log', 'compliance', 'security'],
            'Settings' => ['settings', 'system', 'general', 'maintenance', 'legal'],
            default => str($label)->lower()->explode(' ')->all(),
        };
    }

    /**
     * @param  array<int, string>  $keywords
     * @return array{id: string, title: string, description: string, group: string, route: string, keywords: array<int, string>, permission: string, danger: bool, icon: string}
     */
    private function command(
        string $title,
        string $description,
        string $group,
        string $route,
        array $keywords,
        string $permission,
        string $icon,
        bool $danger = false,
    ): array {
        $id = str($title)->slug()->toString();

        return compact('id', 'title', 'description', 'group', 'route', 'keywords', 'permission', 'danger', 'icon');
    }
}
