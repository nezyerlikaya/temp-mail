<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\User;

class PlanImpactService
{
    /** @return array<int, array{label: string, value: string}> */
    public function preview(Plan $plan): array
    {
        $limits = $plan->limits;
        $users = User::query()->where('current_plan_reference', $plan->key)->count();

        return [
            ['label' => 'Assigned users', 'value' => $users.' account(s) reference this plan. No role changes are made.'],
            ['label' => 'Inbox capacity', 'value' => $limits->maximum_active_inboxes.' active inboxes for future enforcement.'],
            ['label' => 'Inbox lifetime', 'value' => $limits->inbox_lifetime_minutes.' minutes per inbox.'],
            ['label' => 'Messages', 'value' => $limits->maximum_messages_per_inbox.' messages per inbox, '.$limits->maximum_message_size_kb.' KB max size readiness.'],
            ['label' => 'Capabilities', 'value' => ($limits->custom_alias_allowed ? 'Custom alias' : 'System alias').' · '.($limits->custom_domain_allowed ? 'Custom domain' : 'Shared domains').' · '.($limits->api_access_allowed ? 'API ready' : 'No API')],
        ];
    }
}
