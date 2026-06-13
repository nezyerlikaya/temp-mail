<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Services\Updates\UpdateChannelResolver;
use App\Services\Users\RolePermissionResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class DashboardSummaryService
{
    public function __construct(
        private readonly DashboardMetricService $metrics,
        private readonly DashboardHealthSummaryService $health,
        private readonly DashboardActivityService $activity,
        private readonly DashboardAlertService $alerts,
        private readonly RolePermissionResolver $permissions,
        private readonly UpdateChannelResolver $channels,
    ) {}

    /** @return array<string, mixed> */
    public function summary(User $user): array
    {
        $role = $this->permissions->roleFor($user)->value;

        return Cache::remember('dashboard.operations.summary.'.$role, now()->addSeconds(20), fn (): array => [
            'metrics' => $this->metrics->metrics($this->includeSensitive($user)),
            'health' => $this->health->items(),
            'alerts' => $this->alerts->alerts($this->includeSensitive($user)),
            'activity' => $this->activity->recent($user),
            'quick_actions' => $this->quickActions($user),
            'last_updated' => now(),
            'cache_seconds' => 20,
        ]);
    }

    private function includeSensitive(User $user): bool
    {
        return Gate::forUser($user)->allows('admin.security-defense-center.view')
            || Gate::forUser($user)->allows('admin.activity-audit-logs.view');
    }

    /** @return array<int, array<string, mixed>> */
    private function quickActions(User $user): array
    {
        return collect([
            $this->action('Create backup', 'admin.backups-health.store', 'POST', 'database-backup', 'Create a fresh system backup.', Gate::forUser($user)->allows('admin.backups-health.create'), ['type' => 'database']),
            $this->action('Check for updates', 'admin.update-center.check', 'POST', 'refresh-cw', 'Run the update manifest check.', Gate::forUser($user)->allows('admin.update-center.check'), ['channel' => $this->channels->default()]),
            $this->action('Open Mailbox Operations', 'admin.mailbox-operations.index', 'GET', 'inbox', 'Review inbox and message operations.', Gate::forUser($user)->allows('admin.mailbox-operations.view')),
            $this->action('Review pending comments', 'admin.comment-moderation.index', 'GET', 'messages-square', 'Open the comment moderation queue.', Gate::forUser($user)->allows('admin.comment-moderation.view')),
            $this->action('Review security alerts', 'admin.security-defense-center.index', 'GET', 'shield-alert', 'Open security signals and abuse monitoring.', Gate::forUser($user)->allows('admin.security-defense-center.view')),
        ])->filter(fn (array $action): bool => $action['allowed'])->values()->all();
    }

    /** @return array<string, mixed> */
    private function action(string $label, string $route, string $method, string $icon, string $description, bool $allowed, array $payload = []): array
    {
        return compact('label', 'route', 'method', 'icon', 'description', 'allowed', 'payload');
    }
}
