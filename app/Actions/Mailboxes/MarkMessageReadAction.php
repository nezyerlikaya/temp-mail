<?php

namespace App\Actions\Mailboxes;

use App\Models\MailboxMessage;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class MarkMessageReadAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handle(User $actor, MailboxMessage $message, bool $read): MailboxMessage
    {
        $message->forceFill(['read_at' => $read ? now() : null])->save();
        $this->audit->record('mailbox.message_state_updated', $actor, null, [
            'mailbox_id' => $message->mailbox_id, 'message_id' => $message->id, 'state' => $read ? 'read' : 'unread',
        ], ['module' => 'mailbox', 'action' => 'Message state updated', 'target' => $message]);

        return $message->refresh();
    }
}
