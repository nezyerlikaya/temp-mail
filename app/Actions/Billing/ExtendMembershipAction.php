<?php

namespace App\Actions\Billing;

use App\Models\Membership;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Billing\MembershipService;
use App\Services\Billing\MembershipStatusResolver;
use Illuminate\Support\Carbon;

class ExtendMembershipAction
{
    public function __construct(
        private readonly MembershipService $memberships,
        private readonly MembershipStatusResolver $statuses,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, Membership $membership, array $data): Membership
    {
        $endsAt = ($data['preset'] ?? null) === 'one_month'
            ? $this->memberships->oneMonthEndsAt($membership->ends_at?->isFuture() ? $membership->ends_at : now())
            : Carbon::parse($data['ends_at']);

        $membership->forceFill([
            'ends_at' => $endsAt,
            'status' => 'active',
            'grace_period_days' => (int) ($data['grace_period_days'] ?? $membership->grace_period_days),
            'canceled_at' => null,
        ])->save();

        $membership->forceFill(['status' => $this->statuses->resolve($membership)])->save();
        $membership = $membership->refresh()->load('plan', 'user');
        $this->memberships->syncUser($membership->user, $membership);
        $this->audit->record('membership.extended', $actor, $membership->user, [
            'membership_id' => $membership->id,
            'plan_key' => $membership->plan->key,
            'ends_at' => $membership->ends_at?->toIso8601String(),
        ], ['module' => 'billing', 'action' => 'Membership extended', 'target' => $membership]);

        return $membership;
    }
}
