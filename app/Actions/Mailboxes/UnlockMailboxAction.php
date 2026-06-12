<?php

namespace App\Actions\Mailboxes;

use App\Models\Mailbox;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mailboxes\MailboxLifecycleService;

class UnlockMailboxAction
{
    public function __construct(private readonly MailboxLifecycleService $lifecycle, private readonly AuditLogger $audit) {}

    public function handle(User $actor, Mailbox $mailbox): Mailbox
    {
        abort_unless($mailbox->status === 'locked', 422, 'Only locked mailboxes can be unlocked.');
        $mailbox->forceFill(['status' => 'active'])->save();
        $mailbox = $this->lifecycle->record($mailbox, 'mailbox_unlocked', 'Mailbox unlocked', 'Inbound activity was restored by an administrator.');
        $this->audit->record('mailbox.unlocked', $actor, null, ['mailbox_id' => $mailbox->id], ['module' => 'mailbox', 'action' => 'Mailbox unlocked', 'target' => $mailbox]);

        return $mailbox;
    }
}
