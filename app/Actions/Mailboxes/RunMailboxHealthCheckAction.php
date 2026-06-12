<?php

namespace App\Actions\Mailboxes;

use App\Models\MailboxDeliveryHealthCheck;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mailboxes\MailboxDeliveryHealthService;
use App\Services\Mailboxes\MailboxHealthNotificationDispatcher;

class RunMailboxHealthCheckAction
{
    public function __construct(
        private readonly MailboxDeliveryHealthService $health,
        private readonly MailboxHealthNotificationDispatcher $notifications,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor): MailboxDeliveryHealthCheck
    {
        $summary = $this->health->summary();
        $check = MailboxDeliveryHealthCheck::query()->create([
            'status' => $summary['status'], 'summary' => $summary, 'checked_at' => now(), 'checked_by' => $actor->id,
        ]);
        $this->notifications->dispatch($summary);
        $this->audit->record('mailbox.delivery_health_checked', $actor, null, [
            'status' => $summary['status'],
            'card_statuses' => collect($summary['cards'])->pluck('status', 'label')->all(),
        ], ['module' => 'mailbox', 'action' => 'Delivery health checked', 'target' => $check]);

        return $check;
    }
}
