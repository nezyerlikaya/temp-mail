<?php

namespace App\Actions\Billing;

use App\Models\Plan;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Billing\PlanSettingsStore;
use Illuminate\Validation\ValidationException;

class DeactivatePlanAction
{
    public function __construct(private readonly PlanSettingsStore $store, private readonly AuditLogger $audit) {}

    public function handle(User $actor, Plan $plan): Plan
    {
        if ($plan->key === 'free') {
            throw ValidationException::withMessages(['plan' => 'The Free plan cannot be disabled.']);
        }

        if ($plan->is_active && $this->store->activePublicCount() <= 1) {
            throw ValidationException::withMessages(['plan' => 'At least one public plan must remain active.']);
        }

        $plan->forceFill(['is_active' => false, 'updated_by' => $actor->id])->save();
        $this->audit->record('plan.deactivated', $actor, null, [
            'plan_key' => $plan->key,
        ], ['module' => 'billing', 'action' => 'Plan deactivated', 'target' => $plan]);

        return $plan->refresh();
    }
}
