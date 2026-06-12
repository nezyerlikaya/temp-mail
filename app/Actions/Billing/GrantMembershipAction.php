<?php

namespace App\Actions\Billing;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Billing\MembershipService;
use App\Services\Billing\MembershipStatusResolver;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GrantMembershipAction
{
    public function __construct(
        private readonly MembershipService $memberships,
        private readonly MembershipStatusResolver $statuses,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, array $data): Membership
    {
        $user = User::query()->findOrFail((int) $data['user_id']);
        $plan = Plan::query()->where('key', '!=', 'free')->findOrFail((int) $data['plan_id']);
        $startsAt = Carbon::parse($data['starts_at'] ?? now());
        $endsAt = ($data['preset'] ?? null) === 'one_month'
            ? $this->memberships->oneMonthEndsAt($startsAt)
            : Carbon::parse($data['ends_at']);

        return DB::transaction(function () use ($actor, $data, $user, $plan, $startsAt, $endsAt): Membership {
            $user->memberships()->whereIn('status', ['active', 'expiring'])->update(['status' => 'canceled', 'canceled_at' => now(), 'updated_at' => now()]);
            $membership = Membership::query()->create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'granted_by' => $actor->id,
                'grant_note' => $data['grant_note'] ?? null,
                'grace_period_days' => (int) ($data['grace_period_days'] ?? 0),
            ])->load('plan', 'user');

            $membership->forceFill(['status' => $this->statuses->resolve($membership)])->save();
            $this->memberships->syncUser($user, $membership->refresh()->load('plan'));
            $this->audit->record('membership.granted', $actor, $user, [
                'membership_id' => $membership->id,
                'plan_key' => $plan->key,
                'preset' => $data['preset'] ?? 'custom',
                'ends_at' => $membership->ends_at?->toIso8601String(),
            ], ['module' => 'billing', 'action' => 'Membership granted', 'target' => $membership]);

            return $membership;
        });
    }
}
