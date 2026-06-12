<?php

namespace App\Services\Mailboxes;

use App\Models\MailboxRule;
use App\Models\User;

class MailboxRulesStore
{
    public function current(): MailboxRule
    {
        return MailboxRule::query()->firstOrCreate([], $this->defaults());
    }

    /** @param array<string, mixed> $data */
    public function update(array $data, User $actor): MailboxRule
    {
        $rule = $this->current();
        $rule->update([...$data, 'auto_delete_expired' => (bool) ($data['auto_delete_expired'] ?? false), 'updated_by' => $actor->id]);

        return $rule->refresh();
    }

    /** @return array<string, mixed> */
    private function defaults(): array
    {
        return [
            'guest_lifetime_minutes' => 1440, 'registered_lifetime_minutes' => 10080,
            'premium_lifetime_minutes' => 43200, 'maximum_active_mailboxes' => 10,
            'maximum_messages_per_inbox' => 100, 'maximum_message_size_kb' => 10240,
            'attachment_policy' => 'disabled', 'auto_delete_expired' => false,
            'expired_cleanup_delay_hours' => 24, 'inbox_refresh_rate_limit' => 30,
            'random_alias_length' => 12, 'random_alias_format' => 'alphanumeric',
        ];
    }
}
