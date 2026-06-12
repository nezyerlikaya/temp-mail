<?php

namespace App\Services\Billing;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MembershipService
{
    public function __construct(private readonly PlanSettingsStore $plans, private readonly MembershipStatusResolver $statuses) {}

    /** @param array<string, mixed> $filters */
    public function search(array $filters = []): LengthAwarePaginator
    {
        return Membership::query()
            ->with(['user', 'plan', 'grantor'])
            ->when(($filters['plan_id'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('plan_id', $filters['plan_id']))
            ->when(($filters['status'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(($filters['expiring'] ?? 'all') === 'soon', fn (Builder $query) => $query->whereIn('status', ['active', 'expiring'])->whereNotNull('ends_at')->where('ends_at', '<=', now()->addDays(7))->where('ends_at', '>', now()))
            ->when(filled($filters['user'] ?? null), fn (Builder $query) => $query->whereHas('user', fn (Builder $user) => $user->where('email', 'like', '%'.$filters['user'].'%')->orWhere('name', 'like', '%'.$filters['user'].'%')))
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }

    public function activeFor(User $user): ?Membership
    {
        return $user->memberships()->with('plan')->whereIn('status', ['active', 'expiring'])->latest('ends_at')->first();
    }

    public function syncUser(User $user, ?Membership $membership): User
    {
        if (! $membership || in_array($membership->status, ['expired', 'canceled'], true)) {
            $user->forceFill([
                'current_plan_reference' => 'free',
                'membership_status' => $membership?->status ?? 'expired',
                'premium_starts_at' => null,
                'premium_ends_at' => null,
                'membership_granted_by' => null,
            ])->save();

            return $user->refresh();
        }

        $user->forceFill([
            'current_plan_reference' => $membership->plan->key,
            'membership_status' => $membership->status,
            'premium_starts_at' => $membership->starts_at,
            'premium_ends_at' => $membership->ends_at,
            'membership_granted_by' => $membership->granted_by,
        ])->save();

        return $user->refresh();
    }

    public function oneMonthEndsAt(?Carbon $start = null): Carbon
    {
        return ($start ?: now())->copy()->addMonthNoOverflow();
    }

    public function premiumPlan(): Plan
    {
        $this->plans->ensureDefaults();

        return Plan::query()->where('key', 'premium')->firstOrFail();
    }

    /** @return array<string, string> */
    public function statuses(): array
    {
        return ['active' => 'Active', 'expiring' => 'Expiring', 'expired' => 'Expired', 'canceled' => 'Canceled'];
    }
}
