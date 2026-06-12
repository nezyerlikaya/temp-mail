<?php

namespace App\Actions\Mailboxes;

use App\Models\Mailbox;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mailboxes\MailboxLifecycleService;

class ExpireMailboxAction
{
    public function __construct(private readonly MailboxLifecycleService $lifecycle, private readonly AuditLogger $audit) {}

    public function handle(User $actor, Mailbox $mailbox): Mailbox
    {
        $mailbox->forceFill(['status' => 'expired', 'expires_at' => now()])->save();
        $mailbox = $this->lifecycle->record($mailbox, 'mailbox_expired', 'Mailbox expired', 'An administrator expired this mailbox immediately.');
        $this->audit->record('mailbox.expired', $actor, null, ['mailbox_id' => $mailbox->id], ['module' => 'mailbox', 'action' => 'Mailbox expired', 'target' => $mailbox]);

        return $mailbox;
    }
}
