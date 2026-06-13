<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Services\Updates\UpdateChannelResolver;
use App\Services\Users\RolePermissionResolver;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Throwable;

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
        $key = 'dashboard.operations.summary.'.$role;
        $seconds = 20;

        $summary = Cache::get($key);

        if (! is_array($summary) || ! $this->normalizeLastUpdated($summary['last_updated'] ?? null) instanceof Carbon) {
            $summary = $this->buildSummary($user);
            Cache::put($key, $summary, now()->addSeconds($seconds));
        }

        $summary['last_updated'] = $this->normalizeLastUpdated($summary['last_updated']);

        return $summary;
    }

    /** @return array<string, mixed> */
    private function buildSummary(User $user): array
    {
        return [
            'metrics' => $this->metrics->metrics($this->includeSensitive($user)),
            'health' => $this->health->items(),
            'alerts' => $this->alerts->alerts($this->includeSensitive($user)),
            'activity' => $this->activity->recent($user),
            'quick_actions' => $this->quickActions($user),
            'last_updated' => now()->toIso8601String(),
            'cache_seconds' => 20,
        ];
    }

    private function normalizeLastUpdated(mixed $value): ?Carbon
    {
        try {
            if ($value instanceof Carbon) {
                return $value;
            }

            if ($value instanceof DateTimeInterface) {
                return Carbon::instance($value);
            }

            if (is_int($value)) {
                return Carbon::createFromTimestamp($value);
            }

            if (is_string($value) && $value !== '') {
                return Carbon::parse($value);
            }
        } catch (Throwable) {
            return null;
        }

        return null;
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
