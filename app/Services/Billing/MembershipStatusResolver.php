<?php

namespace App\Services\Billing;

use App\Models\Membership;

class MembershipStatusResolver
{
    public function resolve(Membership $membership): string
    {
        if ($membership->canceled_at !== null || $membership->status === 'canceled') {
            return 'canceled';
        }

        if ($membership->ends_at?->isPast()) {
            return 'expired';
        }

        if ($membership->ends_at?->lte(now()->addDays(7))) {
            return 'expiring';
        }

        return 'active';
    }
}
