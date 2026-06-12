<?php

namespace App\Services\Notifications;

use App\Models\SystemNotification;
use App\Models\User;

class NotificationDeduplicationService
{
    /** @param array<string, mixed> $payload */
    public function createOrIncrement(User $recipient, array $payload): SystemNotification
    {
        $key = $this->key($payload);

        $existing = SystemNotification::query()
            ->where('recipient_user_id', $recipient->getKey())
            ->where('deduplication_key', $key)
            ->whereNull('archived_at')
            ->first();

        if ($existing !== null) {
            $existing->forceFill([
                'occurrence_count' => max(1, (int) $existing->occurrence_count) + 1,
                'last_occurred_at' => now(),
                'read_at' => null,
                'message' => (string) $payload['message'],
            ])->save();

            return $existing;
        }

        return SystemNotification::query()->create([
            'recipient_user_id' => $recipient->getKey(),
            'event_key' => (string) $payload['event_key'],
            'type' => (string) ($payload['type'] ?? $payload['event_key']),
            'severity' => (string) ($payload['severity'] ?? 'info'),
            'title' => (string) $payload['title'],
            'message' => (string) $payload['message'],
            'related_module' => $payload['related_module'] ?? null,
            'target_type' => $payload['target_type'] ?? null,
            'target_id' => $payload['target_id'] ?? null,
            'action_route' => $payload['action_route'] ?? null,
            'action_parameters' => $payload['action_parameters'] ?? [],
            'action_url' => null,
            'occurrence_count' => 1,
            'first_occurred_at' => now(),
            'last_occurred_at' => now(),
            'deduplication_key' => $key,
        ]);
    }

    /** @param array<string, mixed> $payload */
    private function key(array $payload): string
    {
        return hash('sha256', implode('|', [
            (string) ($payload['event_key'] ?? ''),
            (string) ($payload['related_module'] ?? ''),
            (string) ($payload['target_type'] ?? ''),
            (string) ($payload['target_id'] ?? ''),
            (string) ($payload['action_route'] ?? ''),
        ]));
    }
}
