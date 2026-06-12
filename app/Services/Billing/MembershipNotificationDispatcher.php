<?php

namespace App\Services\Billing;

use App\Models\Membership;
use App\Services\Notifications\NotificationService;

class MembershipNotificationDispatcher
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function expiringSoon(Membership $membership): void
    {
        $this->notifications->dispatch([
            'event_key' => 'premium_expiring_soon',
            'type' => 'membership',
            'severity' => 'warning',
            'title' => 'Premium membership expiring soon',
            'message' => $membership->user->email.' has a membership ending soon.',
            'related_module' => 'billing',
            'target_type' => Membership::class,
            'target_id' => $membership->id,
            'action_route' => 'admin.plans-memberships.index',
        ], sendEmail: false);
    }

    public function expired(Membership $membership): void
    {
        $this->notifications->dispatch([
            'event_key' => 'premium_expired',
            'type' => 'membership',
            'severity' => 'warning',
            'title' => 'Premium membership expired',
            'message' => $membership->user->email.' was downgraded to Free after membership expiration.',
            'related_module' => 'billing',
            'target_type' => Membership::class,
            'target_id' => $membership->id,
            'action_route' => 'admin.plans-memberships.index',
        ], sendEmail: false);
    }
}
