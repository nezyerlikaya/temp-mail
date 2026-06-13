<?php

namespace App\Actions\Mailboxes;

use App\Models\Mailbox;
use App\Models\User;
use App\Services\Analytics\AnalyticsEventTracker;
use App\Services\Audit\AuditLogger;
use App\Services\Mailboxes\MailboxLifecycleService;

class ExpireMailboxAction
{
    public function __construct(
        private readonly MailboxLifecycleService $lifecycle,
        private readonly AuditLogger $audit,
        private readonly AnalyticsEventTracker $analytics,
    ) {}

    public function handle(User $actor, Mailbox $mailbox): Mailbox
    {
        $mailbox->forceFill(['status' => 'expired', 'expires_at' => now()])->save();
        $mailbox = $this->lifecycle->record($mailbox, 'mailbox_expired', 'Mailbox expired', 'An administrator expired this mailbox immediately.');
        $this->audit->record('mailbox.expired', $actor, null, ['mailbox_id' => $mailbox->id], ['module' => 'mailbox', 'action' => 'Mailbox expired', 'target' => $mailbox]);
        $this->analytics->trackSafely('mailbox.expired', [
            'user' => $mailbox->user_id,
            'locale_id' => $mailbox->locale_id,
            'domain_id' => $mailbox->domain_id,
            'metadata' => ['source' => 'admin', 'mailbox_type' => $mailbox->mailbox_type],
        ]);

        return $mailbox;
    }
}
