<?php

namespace App\Actions\Billing;

use App\Models\Plan;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Billing\PlanSettingsStore;

class UpdatePlanAction
{
    public function __construct(private readonly PlanSettingsStore $store, private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $data */
    public function details(User $actor, Plan $plan, array $data): Plan
    {
        $plan = $this->store->updatePlan($plan, $data, $actor);
        $this->audit->record('plan.updated', $actor, null, [
            'plan_key' => $plan->key,
            'monthly_price' => $plan->monthly_price,
            'yearly_price' => $plan->yearly_price,
            'currency' => $plan->currency,
            'billing_provider' => 'manual',
        ], ['module' => 'billing', 'action' => 'Plan updated', 'target' => $plan]);

        return $plan;
    }

    /** @param array<string, mixed> $data */
    public function limits(User $actor, Plan $plan, array $data): Plan
    {
        $plan = $this->store->updateLimits($plan, $data);
        $this->audit->record('plan.limits_updated', $actor, null, [
            'plan_key' => $plan->key,
            'maximum_active_inboxes' => $plan->limits->maximum_active_inboxes,
            'inbox_lifetime_minutes' => $plan->limits->inbox_lifetime_minutes,
            'maximum_messages_per_inbox' => $plan->limits->maximum_messages_per_inbox,
            'api_access_allowed' => $plan->limits->api_access_allowed,
        ], ['module' => 'billing', 'action' => 'Plan limits updated', 'target' => $plan]);

        return $plan;
    }
}
