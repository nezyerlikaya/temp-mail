<?php

namespace App\Services\Api;

use App\Models\ApiKey;
use App\Models\User;
use App\Services\Billing\PlanLimitResolver;

class ApiAccessPolicyService
{
    public function __construct(
        private readonly ApiSettingsStore $settings,
        private readonly PlanLimitResolver $limits,
    ) {}

    public function globalEnabled(): bool
    {
        return (bool) $this->settings->get()['api_enabled'];
    }

    public function planAllows(User $user): bool
    {
        $planKey = $user->current_plan_reference ?: 'free';
        $settings = $this->settings->get();

        if (! (bool) ($settings[$planKey.'_api_enabled'] ?? false)) {
            return false;
        }

        return $this->limits->forUser($user)->api_access_allowed;
    }

    public function canViewKeys(User $actor): bool
    {
        return $actor->can('admin.api-access.view');
    }

    public function canManageGlobally(User $actor): bool
    {
        return $actor->can('admin.api-access.manage');
    }

    public function canCreateFor(User $actor, User $owner): bool
    {
        if (! $this->globalEnabled() || ! $this->planAllows($owner)) {
            return false;
        }

        return $actor->is($owner) || $this->canManageGlobally($actor);
    }

    public function canMutate(User $actor, ApiKey $key): bool
    {
        return $actor->is($key->user) || $this->canManageGlobally($actor);
    }

    public function statusFor(ApiKey $key): string
    {
        if ($key->revoked_at !== null || $key->status === 'revoked') {
            return 'revoked';
        }

        if ($key->isExpired()) {
            return 'expired';
        }

        return 'active';
    }
}
