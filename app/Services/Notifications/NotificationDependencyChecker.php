<?php

namespace App\Services\Notifications;

use App\Models\NotificationRule;

class NotificationDependencyChecker
{
    /** @param iterable<int, NotificationRule> $rules */
    public function warnings(iterable $rules): array
    {
        $warnings = [];

        foreach ($rules as $rule) {
            if ($rule->email_enabled && (! filled(config('mail.default')) || ! filled(config('mail.from.address')))) {
                $warnings[] = [
                    'event_key' => $rule->event_key,
                    'severity' => 'warning',
                    'message' => 'Email channel is enabled, but mail delivery is not fully configured.',
                ];
            }

            if (! in_array($rule->event_key, array_keys(app(NotificationRuleStore::class)->defaults()), true)) {
                $warnings[] = [
                    'event_key' => $rule->event_key,
                    'severity' => 'warning',
                    'message' => 'This event does not map to a known MVP module.',
                ];
            }
        }

        return $warnings;
    }
}
