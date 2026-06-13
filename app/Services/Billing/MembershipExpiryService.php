<?php

namespace App\Services\Billing;

use App\Models\Membership;
use App\Models\User;
use App\Services\Analytics\AnalyticsEventTracker;
use App\Services\Audit\AuditLogger;

class MembershipExpiryService
{
    public function __construct(
        private readonly MembershipService $memberships,
        private readonly MembershipNotificationDispatcher $notifications,
        private readonly AuditLogger $audit,
        private readonly AnalyticsEventTracker $analytics,
    ) {}

    /** @return array{expired: int, expiring: int} */
    public function process(?User $actor = null): array
    {
        $expired = 0;
        $expiring = 0;

        Membership::query()->with(['user', 'plan'])->whereIn('status', ['active', 'expiring'])->whereNotNull('ends_at')->get()
            ->each(function (Membership $membership) use (&$expired, &$expiring, $actor): void {
                $effectiveEnd = $membership->ends_at?->copy()->addDays($membership->grace_period_days);

                if ($effectiveEnd?->isPast()) {
                    $membership->forceFill(['status' => 'expired'])->save();
                    $this->memberships->syncUser($membership->user, $membership->refresh()->load('plan'));
                    $this->notifications->expired($membership);
                    $this->audit->record('membership.expired', $actor, $membership->user, [
                        'membership_id' => $membership->id,
                        'plan_key' => $membership->plan->key,
                    ], ['module' => 'billing', 'action' => 'Membership expired', 'target' => $membership]);
                    $this->audit->record('membership.user_downgraded_to_free', $actor, $membership->user, [
                        'membership_id' => $membership->id,
                        'previous_plan_key' => $membership->plan->key,
                    ], ['module' => 'billing', 'action' => 'User downgraded to Free', 'target' => $membership]);
                    $this->analytics->trackSafely('premium.expired', [
                        'user' => $membership->user,
                        'metadata' => [
                            'source' => 'billing',
                            'plan_key' => $membership->plan->key,
                            'status' => $membership->status,
                        ],
                    ]);
                    $expired++;

                    return;
                }

                if ($membership->ends_at?->between(now(), now()->addDays(7)) && $membership->status !== 'expiring') {
                    $membership->forceFill(['status' => 'expiring'])->save();
                    $this->memberships->syncUser($membership->user, $membership->refresh()->load('plan'));
                    $this->notifications->expiringSoon($membership);
                    $expiring++;
                }
            });

        return ['expired' => $expired, 'expiring' => $expiring];
    }
}
