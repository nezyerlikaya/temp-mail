<?php

namespace App\Services\Api;

use App\Models\ApiKey;
use App\Services\Billing\PlanLimitResolver;

class ApiRateLimitResolver
{
    public function __construct(private readonly PlanLimitResolver $limits, private readonly ApiUsageTracker $usage) {}

    public function monthlyLimit(ApiKey $key): int
    {
        return max(0, (int) $this->limits->forUser($key->user)->api_request_limit);
    }

    public function isExceeded(ApiKey $key): bool
    {
        $limit = $this->monthlyLimit($key);

        if ($limit <= 0) {
            return true;
        }

        return $this->usage->summary($key->user, $limit)['requests_this_month'] >= $limit;
    }
}
