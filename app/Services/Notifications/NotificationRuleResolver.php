<?php

namespace App\Services\Notifications;

use App\Models\NotificationRule;

class NotificationRuleResolver
{
    public function __construct(private readonly NotificationRuleStore $store) {}

    /** @param array<string, mixed> $payload */
    public function resolve(array $payload): NotificationRule
    {
        $this->store->ensureDefaults();

        $eventKey = (string) $payload['event_key'];
        $rule = NotificationRule::query()->where('event_key', $eventKey)->first();

        if ($rule !== null) {
            return $this->applyCriticalOverrides($rule);
        }

        $defaults = $this->store->defaults()[$eventKey] ?? [
            'severity' => $payload['severity'] ?? 'info',
            'recipient_roles' => ['owner', 'admin'],
            'in_app_enabled' => true,
            'email_enabled' => false,
            'digest_mode' => 'immediate',
        ];

        return new NotificationRule([
            'event_key' => $eventKey,
            'severity' => (string) $defaults['severity'],
            'recipient_roles' => $defaults['recipient_roles'],
            'in_app_enabled' => (bool) $defaults['in_app_enabled'],
            'email_enabled' => (bool) $defaults['email_enabled'],
            'digest_mode' => (string) $defaults['digest_mode'],
            'quiet_hours_enabled' => false,
            'is_active' => true,
        ]);
    }

    public function isCritical(NotificationRule $rule): bool
    {
        return $rule->severity === 'critical'
            || in_array($rule->event_key, ['failed_admin_login', 'security_setting_changed', 'backup_failed'], true);
    }

    private function applyCriticalOverrides(NotificationRule $rule): NotificationRule
    {
        if (! $this->isCritical($rule)) {
            return $rule;
        }

        $rule->forceFill([
            'in_app_enabled' => true,
            'digest_mode' => 'immediate',
            'quiet_hours_enabled' => false,
            'is_active' => true,
            'recipient_roles' => ['owner', 'admin'],
        ]);

        return $rule;
    }
}
