<?php

namespace App\Services\Users;

use App\Models\User;

class MembershipSummaryResolver
{
    /** @return array{plan: string, status: string, label: string, starts_at: string|null, ends_at: string|null, granted_by: string|null, ready: bool} */
    public function resolve(User $user): array
    {
        $membership = $user->memberships()->with(['plan', 'grantor'])->latest()->first();
        $status = $this->effectiveStatus($user);

        return [
            'plan' => $membership?->plan?->name ?? ($user->current_plan_reference ?: 'No plan connected'),
            'status' => $status,
            'label' => str($status)->headline()->toString(),
            'starts_at' => ($membership?->starts_at ?? $user->premium_starts_at)?->format('M j, Y H:i'),
            'ends_at' => ($membership?->ends_at ?? $user->premium_ends_at)?->format('M j, Y H:i'),
            'granted_by' => $membership?->grantor?->name ?? ($user->membership_granted_by
                ? User::query()->find($user->membership_granted_by)?->name
                : null),
            'ready' => $membership !== null || $user->current_plan_reference !== null,
        ];
    }

    private function effectiveStatus(User $user): string
    {
        if ($user->premium_ends_at?->isPast()) {
            return 'expired';
        }

        if ($user->premium_starts_at?->isFuture()) {
            return 'scheduled';
        }

        return $user->membership_status ?: 'none';
    }
}
