<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\PlanLimit;
use App\Models\User;

class PlanLimitResolver
{
    public function __construct(private readonly PlanSettingsStore $store) {}

    public function forUser(User $user): PlanLimit
    {
        $this->store->ensureDefaults();
        $key = $user->current_plan_reference ?: 'free';
        $plan = Plan::query()->with('limits')->where('key', $key)->where('is_active', true)->first()
            ?: Plan::query()->with('limits')->where('key', 'free')->firstOrFail();

        return $plan->limits;
    }
}
