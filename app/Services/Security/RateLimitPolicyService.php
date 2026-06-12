<?php

namespace App\Services\Security;

class RateLimitPolicyService
{
    public function __construct(private readonly RateLimitPolicyStore $store) {}

    /** @return array<int, array<string, mixed>> */
    public function readiness(): array
    {
        return collect($this->store->policies())->map(function (array $policy): array {
            $status = $policy['is_active'] ? 'ready' : 'passive';

            if ($policy['max_attempts'] < 1 || $policy['window_minutes'] < 1) {
                $status = 'failed';
            }

            return [
                'label' => $policy['label'],
                'status' => $status,
                'message' => $policy['is_active']
                    ? "{$policy['max_attempts']} attempts per {$policy['window_minutes']} minute window"
                    : 'Policy is saved but passive.',
            ];
        })->values()->all();
    }

    public function summaryStatus(): string
    {
        $active = collect($this->store->policies())->filter(fn (array $policy): bool => $policy['is_active'])->count();

        return $active >= 4 ? 'ready' : 'passive';
    }
}
