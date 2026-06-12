<?php

namespace App\Actions\Billing;

use App\Models\Membership;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Billing\MembershipService;

class CancelMembershipAction
{
    public function __construct(private readonly MembershipService $memberships, private readonly AuditLogger $audit) {}

    public function handle(User $actor, Membership $membership, ?string $reason = null): Membership
    {
        $membership->forceFill(['status' => 'canceled', 'canceled_at' => now()])->save();
        $membership = $membership->refresh()->load('plan', 'user');
        $this->memberships->syncUser($membership->user, $membership);
        $this->audit->record('membership.canceled', $actor, $membership->user, [
            'membership_id' => $membership->id,
            'plan_key' => $membership->plan->key,
            'reason_present' => filled($reason),
        ], ['module' => 'billing', 'action' => 'Membership canceled', 'target' => $membership]);

        return $membership;
    }
}
