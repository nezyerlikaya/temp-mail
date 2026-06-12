<?php

namespace App\Actions\Billing;

use App\Models\Plan;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class ActivatePlanAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handle(User $actor, Plan $plan): Plan
    {
        $plan->forceFill(['is_active' => true, 'updated_by' => $actor->id])->save();
        $this->audit->record('plan.activated', $actor, null, [
            'plan_key' => $plan->key,
        ], ['module' => 'billing', 'action' => 'Plan activated', 'target' => $plan]);

        return $plan->refresh();
    }
}
