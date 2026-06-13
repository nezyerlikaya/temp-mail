<?php

namespace App\Services\Api;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiKeyService
{
    public function __construct(private readonly ApiScopeRegistry $scopes, private readonly ApiAccessPolicyService $policy) {}

    /** @param array<string, mixed> $data */
    public function create(User $actor, User $owner, array $data): array
    {
        [$prefix, $secret] = $this->secret((string) $data['environment']);

        $key = ApiKey::query()->create([
            'user_id' => $owner->id,
            'name' => $data['name'],
            'environment' => $data['environment'],
            'key_prefix' => $prefix,
            'hashed_secret' => Hash::make($secret),
            'scopes' => $this->scopes->clean($data['scopes'] ?? []),
            'status' => 'active',
            'ip_allowlist' => $data['ip_allowlist'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'created_by' => $actor->id,
        ]);

        return ['key' => $key->load('user'), 'secret' => $secret];
    }

    /** @return array{0: string, 1: string} */
    public function secret(string $environment): array
    {
        $base = $environment === 'live' ? 'tm_live_' : 'tm_test_';
        $prefix = $base.Str::lower(Str::random(10));
        $secret = $prefix.'.'.Str::random(48);

        return [$prefix, $secret];
    }

    public function revoke(ApiKey $key): ApiKey
    {
        $key->forceFill([
            'status' => 'revoked',
            'revoked_at' => now(),
        ])->save();

        return $key->refresh();
    }

    /** @return array{key: ApiKey, secret: string} */
    public function regenerate(ApiKey $key): array
    {
        [$prefix, $secret] = $this->secret($key->environment);

        $key->forceFill([
            'key_prefix' => $prefix,
            'hashed_secret' => Hash::make($secret),
            'status' => 'active',
            'revoked_at' => null,
            'last_used_at' => null,
        ])->save();

        return ['key' => $key->refresh()->load('user'), 'secret' => $secret];
    }

    public function authenticate(string $secret, ?string $ipAddress = null): ?ApiKey
    {
        $prefix = str($secret)->before('.')->toString();

        if ($prefix === '' || ! $this->policy->globalEnabled()) {
            return null;
        }

        $key = ApiKey::query()->with('user')->where('key_prefix', $prefix)->first();

        if (! $key || $this->policy->statusFor($key) !== 'active' || ! Hash::check($secret, $key->hashed_secret)) {
            return null;
        }

        if (! $this->ipAllowed($key, $ipAddress)) {
            return null;
        }

        if (! $this->policy->planAllows($key->user)) {
            return null;
        }

        $key->forceFill(['last_used_at' => now()])->save();

        return $key->refresh();
    }

    private function ipAllowed(ApiKey $key, ?string $ipAddress): bool
    {
        $allowlist = $key->ip_allowlist ?? [];

        return $allowlist === [] || ($ipAddress !== null && in_array($ipAddress, $allowlist, true));
    }
}
