<?php

namespace App\Actions\Mailboxes;

use App\Models\Mailbox;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mailboxes\MailboxLifecycleService;
use Illuminate\Support\Facades\DB;

class EmptyMailboxAction
{
    public function __construct(private readonly MailboxLifecycleService $lifecycle, private readonly AuditLogger $audit) {}

    public function handle(User $actor, Mailbox $mailbox): Mailbox
    {
        $removed = DB::transaction(function () use ($mailbox): int {
            $count = $mailbox->messages()->whereNull('deleted_at')->count();
            $mailbox->messages()->whereNull('deleted_at')->update(['deleted_at' => now(), 'updated_at' => now()]);
            $mailbox->forceFill(['message_count' => 0])->save();

            return $count;
        });

        $mailbox = $this->lifecycle->record($mailbox, 'mailbox_emptied', 'Inbox emptied', $removed.' messages were removed by an administrator.');
        $this->audit->record('mailbox.emptied', $actor, null, ['mailbox_id' => $mailbox->id, 'removed_count' => $removed], ['module' => 'mailbox', 'action' => 'Inbox emptied', 'target' => $mailbox]);

        return $mailbox;
    }
}
