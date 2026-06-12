<?php

namespace App\Actions\Mailboxes;

use App\Models\MailboxRule;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mailboxes\MailboxRulesStore;

class UpdateMailboxRulesAction
{
    public function __construct(private readonly MailboxRulesStore $store, private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, array $data): MailboxRule
    {
        $rules = $this->store->update($data, $actor);
        $this->audit->record('mailbox.rules_updated', $actor, null, [
            'attachment_policy' => $rules->attachment_policy,
            'auto_delete_expired' => $rules->auto_delete_expired,
            'maximum_messages_per_inbox' => $rules->maximum_messages_per_inbox,
            'maximum_message_size_kb' => $rules->maximum_message_size_kb,
        ], ['module' => 'mailbox', 'action' => 'Mailbox rules updated', 'target' => $rules]);

        return $rules;
    }
}
