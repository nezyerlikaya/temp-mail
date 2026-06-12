<?php

namespace App\Services\Notifications;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Collection;

class InAppNotificationDispatcher
{
    public function __construct(
        private readonly NotificationRecipientResolver $recipients,
        private readonly NotificationRuleResolver $rules,
        private readonly NotificationDeduplicationService $deduplication,
        private readonly NotificationDigestService $digest,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, User|int>|null  $recipients
     * @return Collection<int, SystemNotification>
     */
    public function dispatch(array $payload, ?array $recipients = null): Collection
    {
        $eventKey = (string) $payload['event_key'];
        $rule = $this->rules->resolve($payload);
        $severity = $this->rules->isCritical($rule) ? 'critical' : (string) ($payload['severity'] ?? $rule->severity);
        $module = $payload['related_module'] ?? null;

        if (! $rule->is_active || ! $rule->in_app_enabled) {
            return collect();
        }

        $payload['severity'] = $severity;

        return $this->recipients
            ->resolve($eventKey, $severity, $module, $recipients, $rule->recipient_roles ?? null)
            ->map(function (User $recipient) use ($payload, $rule): SystemNotification {
                $notification = $this->deduplication->createOrIncrement($recipient, $payload);

                if ($this->digest->shouldDigest($rule)) {
                    $this->digest->markPending($notification);
                }

                return $notification;
            });
    }
}
