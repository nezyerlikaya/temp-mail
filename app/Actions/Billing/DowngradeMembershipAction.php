<?php

namespace App\Actions\Billing;

use App\Models\Membership;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Billing\MembershipService;

class DowngradeMembershipAction
{
    public function __construct(private readonly MembershipService $memberships, private readonly AuditLogger $audit) {}

    public function handle(User $actor, Membership $membership): Membership
    {
        $membership->forceFill(['status' => 'expired', 'canceled_at' => $membership->canceled_at])->save();
        $membership = $membership->refresh()->load('plan', 'user');
        $this->memberships->syncUser($membership->user, $membership);
        $this->audit->record('membership.user_downgraded_to_free', $actor, $membership->user, [
            'membership_id' => $membership->id,
            'previous_plan_key' => $membership->plan->key,
        ], ['module' => 'billing', 'action' => 'User downgraded to Free', 'target' => $membership]);

        return $membership;
    }
}
