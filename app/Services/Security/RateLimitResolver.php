<?php

namespace App\Services\Security;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class RateLimitResolver
{
    public function __construct(private readonly RateLimitPolicyStore $store) {}

    public function for(string $action, Request $request): Limit
    {
        $policy = $this->store->policy($action);

        if (! $policy['is_active']) {
            return Limit::none();
        }

        return Limit::perMinutes($policy['window_minutes'], $policy['max_attempts'])
            ->by($this->key($action, $policy['strategy'], $request));
    }

    private function key(string $action, string $strategy, Request $request): string
    {
        $identity = match ($strategy) {
            'per_user' => (string) ($request->user()?->getAuthIdentifier() ?: strtolower((string) $request->input('email')) ?: $request->ip()),
            'per_session' => $request->session()->getId() ?: $request->ip(),
            default => $request->ip(),
        };

        return $action.'|'.$strategy.'|'.$identity;
    }
}
