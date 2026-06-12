<?php

namespace App\Services\Mailboxes;

use App\Models\MailboxMessage;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class MailboxPrivacyGuard
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function recordAccess(User $actor, MailboxMessage $message): void
    {
        $this->audit->record('mailbox.message_accessed', $actor, null, [
            'mailbox_id' => $message->mailbox_id,
            'message_id' => $message->id,
        ], ['module' => 'mailbox', 'action' => 'Message content accessed', 'target' => $message]);
    }
}
