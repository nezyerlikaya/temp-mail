<?php

namespace App\Services\Analytics;

use InvalidArgumentException;

class AnalyticsMetricRegistry
{
    /** @return array<string, string> */
    public function events(): array
    {
        return [
            'mailbox.created' => 'Mailbox created',
            'mailbox.email_received' => 'Mailbox email received',
            'mailbox.expired' => 'Mailbox expired',
            'inbox.viewed' => 'Inbox viewed',
            'blog.viewed' => 'Blog viewed',
            'user.registered' => 'User registered',
            'premium.granted' => 'Premium granted',
            'premium.expired' => 'Premium expired',
            'comment.submitted' => 'Comment submitted',
            'security.rate_limited' => 'Security rate limited',
        ];
    }

    public function isRegistered(string $eventKey): bool
    {
        return array_key_exists($eventKey, $this->events());
    }

    public function assertRegistered(string $eventKey): void
    {
        if (! $this->isRegistered($eventKey)) {
            throw new InvalidArgumentException("Analytics event [{$eventKey}] is not registered.");
        }
    }
}
