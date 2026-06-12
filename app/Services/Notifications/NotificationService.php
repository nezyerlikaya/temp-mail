<?php

namespace App\Services\Notifications;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class NotificationService
{
    public function __construct(
        private readonly NotificationRecipientResolver $recipients,
        private readonly NotificationActionLinkResolver $links,
        private readonly InAppNotificationDispatcher $inApp,
        private readonly EmailNotificationDispatcher $email,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, User|int>|null  $recipients
     * @return Collection<int, SystemNotification>
     */
    public function dispatch(array $payload, ?array $recipients = null, bool $sendEmail = true): Collection
    {
        $notifications = $this->inApp->dispatch($payload, $recipients);

        if ($sendEmail) {
            $notifications->each(fn (SystemNotification $notification): bool => $this->email->dispatch($notification));
        }

        return $notifications;
    }

    /** @param array<string, mixed> $filters */
    public function feed(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->visibleQuery($user)
            ->with('recipient')
            ->when(($filters['status'] ?? 'open') === 'unread', fn (Builder $query) => $query->whereNull('read_at')->whereNull('archived_at'))
            ->when(($filters['status'] ?? 'open') === 'read', fn (Builder $query) => $query->whereNotNull('read_at')->whereNull('archived_at'))
            ->when(($filters['status'] ?? 'open') === 'archived', fn (Builder $query) => $query->whereNotNull('archived_at'))
            ->when(($filters['status'] ?? 'open') === 'open', fn (Builder $query) => $query->whereNull('archived_at'))
            ->when(($filters['severity'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('severity', $filters['severity']))
            ->when(($filters['module'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('related_module', $filters['module']))
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    /** @return array{unread: int, critical: int, archived: int, total: int} */
    public function summary(User $user): array
    {
        if (! $this->tableIsReady()) {
            return ['unread' => 0, 'critical' => 0, 'archived' => 0, 'total' => 0];
        }

        $query = $this->visibleQuery($user);

        return [
            'unread' => (clone $query)->whereNull('read_at')->whereNull('archived_at')->count(),
            'critical' => (clone $query)->where('severity', 'critical')->whereNull('archived_at')->count(),
            'archived' => (clone $query)->whereNotNull('archived_at')->count(),
            'total' => (clone $query)->count(),
        ];
    }

    public function unreadCount(User $user): int
    {
        return $this->summary($user)['unread'];
    }

    public function visibleTo(SystemNotification $notification, User $user): bool
    {
        return (int) $notification->recipient_user_id === (int) $user->getKey()
            && $this->recipients->canReceive($user, $notification->related_module);
    }

    /** @return array{label: string, url: string}|null */
    public function actionLink(SystemNotification $notification, User $user): ?array
    {
        return $this->links->resolve($notification, $user);
    }

    public function visibleQuery(User $user): Builder
    {
        $allowedModules = collect(['content', 'trust', 'mail-infrastructure', 'system', 'billing'])
            ->filter(fn (string $module): bool => $this->recipients->canReceive($user, $module))
            ->values()
            ->all();

        return SystemNotification::query()
            ->where('recipient_user_id', $user->getKey())
            ->where(function (Builder $query) use ($allowedModules): void {
                $query->whereNull('related_module')
                    ->orWhereIn('related_module', $allowedModules);
            });
    }

    private function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('system_notifications');
        } catch (Throwable) {
            return false;
        }
    }
}
