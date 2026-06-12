<?php

namespace App\Actions\Mailboxes;

use App\Models\Mailbox;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mailboxes\MailboxLifecycleService;

class LockMailboxAction
{
    public function __construct(private readonly MailboxLifecycleService $lifecycle, private readonly AuditLogger $audit) {}

    public function handle(User $actor, Mailbox $mailbox): Mailbox
    {
        abort_if($mailbox->status === 'expired', 422, 'Expired mailboxes cannot be locked.');
        $mailbox->forceFill(['status' => 'locked'])->save();
        $mailbox = $this->lifecycle->record($mailbox, 'mailbox_locked', 'Mailbox locked', 'Inbound activity was locked by an administrator.');
        $this->audit->record('mailbox.locked', $actor, null, ['mailbox_id' => $mailbox->id], ['module' => 'mailbox', 'action' => 'Mailbox locked', 'target' => $mailbox]);

        return $mailbox;
    }
}
