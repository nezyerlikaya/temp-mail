<?php

namespace App\Actions\Notifications;

use App\Models\NotificationRule;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Notifications\NotificationRuleResolver;
use App\Services\Notifications\NotificationRuleStore;

class UpdateNotificationRulesAction
{
    public function __construct(
        private readonly NotificationRuleStore $store,
        private readonly NotificationRuleResolver $resolver,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, array<string, mixed>> $rules */
    public function handle(User $actor, array $rules): void
    {
        $this->store->ensureDefaults();

        foreach ($rules as $eventKey => $settings) {
            $rule = NotificationRule::query()->where('event_key', $eventKey)->firstOrFail();
            $before = $rule->only(['severity', 'in_app_enabled', 'email_enabled', 'recipient_roles', 'digest_mode', 'quiet_hours_enabled', 'quiet_hours_start', 'quiet_hours_end', 'is_active']);

            $payload = [
                'in_app_enabled' => (bool) ($settings['in_app_enabled'] ?? false),
                'email_enabled' => (bool) ($settings['email_enabled'] ?? false),
                'recipient_roles' => array_values($settings['recipient_roles'] ?? []),
                'digest_mode' => (string) ($settings['digest_mode'] ?? 'immediate'),
                'quiet_hours_enabled' => (bool) ($settings['quiet_hours_enabled'] ?? false),
                'quiet_hours_start' => $settings['quiet_hours_start'] ?? null,
                'quiet_hours_end' => $settings['quiet_hours_end'] ?? null,
                'is_active' => (bool) ($settings['is_active'] ?? false),
            ];

            if ($this->resolver->isCritical($rule)) {
                $payload = [
                    ...$payload,
                    'in_app_enabled' => true,
                    'recipient_roles' => ['owner', 'admin'],
                    'digest_mode' => 'immediate',
                    'quiet_hours_enabled' => false,
                    'quiet_hours_start' => null,
                    'quiet_hours_end' => null,
                    'is_active' => true,
                ];
            }

            $rule->update($payload);

            $after = $rule->fresh()->only(['severity', 'in_app_enabled', 'email_enabled', 'recipient_roles', 'digest_mode', 'quiet_hours_enabled', 'quiet_hours_start', 'quiet_hours_end', 'is_active']);

            if ($before !== $after) {
                $this->audit->record('notification_rule.updated', $actor, $actor, [
                    'event_key' => $eventKey,
                    'before' => $before,
                    'after' => $after,
                ], [
                    'module' => 'notifications',
                    'action' => 'Rule updated',
                    'severity' => $this->resolver->isCritical($rule) ? 'critical' : 'info',
                    'target' => $rule,
                ]);
            }
        }
    }
}
