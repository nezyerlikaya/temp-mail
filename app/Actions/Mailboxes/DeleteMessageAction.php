<?php

namespace App\Actions\Mailboxes;

use App\Models\Mailbox;
use App\Models\MailboxMessage;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class DeleteMessageAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handle(User $actor, Mailbox $mailbox, MailboxMessage $message): MailboxMessage
    {
        $message->forceFill(['deleted_at' => now()])->save();
        $mailbox->forceFill(['message_count' => $mailbox->messages()->whereNull('deleted_at')->count()])->save();
        $this->audit->record('mailbox.message_deleted', $actor, null, [
            'mailbox_id' => $mailbox->id, 'message_id' => $message->id,
        ], ['module' => 'mailbox', 'action' => 'Message deleted', 'target' => $message]);

        return $message->refresh();
    }
}
