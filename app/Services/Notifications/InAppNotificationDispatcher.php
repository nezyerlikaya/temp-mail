<?php

namespace App\Services\Notifications;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Collection;

class InAppNotificationDispatcher
{
    public function __construct(private readonly NotificationRecipientResolver $recipients) {}

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, User|int>|null  $recipients
     * @return Collection<int, SystemNotification>
     */
    public function dispatch(array $payload, ?array $recipients = null): Collection
    {
        $severity = (string) ($payload['severity'] ?? 'info');
        $eventKey = (string) $payload['event_key'];
        $module = $payload['related_module'] ?? null;

        return $this->recipients
            ->resolve($eventKey, $severity, $module, $recipients)
            ->map(fn (User $recipient): SystemNotification => SystemNotification::query()->create([
                'recipient_user_id' => $recipient->getKey(),
                'event_key' => $eventKey,
                'type' => (string) ($payload['type'] ?? $eventKey),
                'severity' => $severity,
                'title' => (string) $payload['title'],
                'message' => (string) $payload['message'],
                'related_module' => $module,
                'target_type' => $payload['target_type'] ?? null,
                'target_id' => $payload['target_id'] ?? null,
                'action_route' => $payload['action_route'] ?? null,
                'action_parameters' => $payload['action_parameters'] ?? [],
                'action_url' => null,
            ]));
    }
}
