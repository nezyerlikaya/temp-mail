<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PlanSettingsStore
{
    /** @return Collection<int, Plan> */
    public function all(): Collection
    {
        $this->ensureDefaults();

        return Plan::query()->with('limits')->orderBy('sort_order')->orderBy('id')->get();
    }

    public function ensureDefaults(): void
    {
        foreach ($this->defaults() as $planData) {
            $limits = $planData['limits'];
            unset($planData['limits']);

            $plan = Plan::query()->firstOrCreate(['key' => $planData['key']], $planData);
            $plan->limits()->firstOrCreate([], $limits);
        }
    }

    /** @param array<string, mixed> $data */
    public function updatePlan(Plan $plan, array $data, User $actor): Plan
    {
        $plan->update([
            ...$data,
            'currency' => str((string) $data['currency'])->upper()->substr(0, 3)->toString(),
            'billing_provider' => 'manual',
            'updated_by' => $actor->id,
        ]);

        return $plan->refresh()->load('limits');
    }

    /** @param array<string, mixed> $data */
    public function updateLimits(Plan $plan, array $data): Plan
    {
        $plan->limits()->updateOrCreate([], $data);

        return $plan->refresh()->load('limits');
    }

    public function activePublicCount(): int
    {
        $this->ensureDefaults();

        return Plan::query()->where('is_active', true)->count();
    }

    /** @return array<int, array<string, mixed>> */
    private function defaults(): array
    {
        return [
            [
                'key' => 'free',
                'name' => 'Free',
                'description' => 'Start with short-lived temp inboxes and essential protection.',
                'is_active' => true,
                'monthly_price' => 0,
                'yearly_price' => 0,
                'currency' => 'USD',
                'sort_order' => 10,
                'billing_provider' => 'manual',
                'limits' => [
                    'maximum_active_inboxes' => 3,
                    'inbox_lifetime_minutes' => 10,
                    'maximum_messages_per_inbox' => 20,
                    'maximum_message_size_kb' => 10240,
                    'custom_alias_allowed' => false,
                    'custom_domain_allowed' => false,
                    'api_access_allowed' => false,
                    'api_request_limit' => 0,
                    'ads_enabled' => true,
                ],
            ],
            [
                'key' => 'premium',
                'name' => 'Premium',
                'description' => 'Longer inbox sessions, custom aliases, and an ad-free personal workflow.',
                'is_active' => true,
                'monthly_price' => 9,
                'yearly_price' => 90,
                'currency' => 'USD',
                'sort_order' => 20,
                'billing_provider' => 'manual',
                'limits' => [
                    'maximum_active_inboxes' => 20,
                    'inbox_lifetime_minutes' => 60,
                    'maximum_messages_per_inbox' => 200,
                    'maximum_message_size_kb' => 20480,
                    'custom_alias_allowed' => true,
                    'custom_domain_allowed' => false,
                    'api_access_allowed' => false,
                    'api_request_limit' => 1000,
                    'ads_enabled' => false,
                ],
            ],
            [
                'key' => 'business',
                'name' => 'Business',
                'description' => 'Team-scale temp mail readiness with domains, API access, and larger inbox limits.',
                'is_active' => true,
                'monthly_price' => 29,
                'yearly_price' => 290,
                'currency' => 'USD',
                'sort_order' => 30,
                'billing_provider' => 'manual',
                'limits' => [
                    'maximum_active_inboxes' => 100,
                    'inbox_lifetime_minutes' => 1440,
                    'maximum_messages_per_inbox' => 1000,
                    'maximum_message_size_kb' => 51200,
                    'custom_alias_allowed' => true,
                    'custom_domain_allowed' => true,
                    'api_access_allowed' => true,
                    'api_request_limit' => 10000,
                    'ads_enabled' => false,
                ],
            ],
        ];
    }
}
