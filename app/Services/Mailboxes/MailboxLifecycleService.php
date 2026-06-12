<?php

namespace App\Services\Mailboxes;

use App\Models\Mailbox;

class MailboxLifecycleService
{
    /** @return array<int, array<string, string>> */
    public function initialTimeline(string $type): array
    {
        return [[
            'event' => 'mailbox_created',
            'label' => 'Mailbox created',
            'detail' => str($type)->headline().' mailbox entered the active lifecycle.',
            'occurred_at' => now()->toIso8601String(),
        ]];
    }

    /** @return array<int, array<string, string>> */
    public function timeline(Mailbox $mailbox): array
    {
        return $mailbox->activity_timeline ?? [];
    }

    public function record(Mailbox $mailbox, string $event, string $label, string $detail): Mailbox
    {
        $timeline = $this->timeline($mailbox);
        $timeline[] = compact('event', 'label', 'detail') + ['occurred_at' => now()->toIso8601String()];

        $mailbox->forceFill(['activity_timeline' => $timeline, 'last_activity_at' => now()])->save();

        return $mailbox->refresh();
    }

    /** @return array<string, string> */
    public function statuses(): array
    {
        return ['active' => 'Active', 'expired' => 'Expired', 'locked' => 'Locked', 'trashed' => 'Trashed'];
    }

    /** @return array<string, string> */
    public function types(): array
    {
        return ['guest' => 'Guest', 'registered' => 'Registered', 'premium' => 'Premium readiness', 'system' => 'System readiness'];
    }
}
