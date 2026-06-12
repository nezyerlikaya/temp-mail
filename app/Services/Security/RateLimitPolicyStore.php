<?php

namespace App\Services\Security;

use App\Models\SecuritySetting;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RateLimitPolicyStore
{
    /** @return array<string, array<string, mixed>> */
    public function policies(): array
    {
        $payload = $this->payload('rate_limits');
        $policies = $payload['policies'] ?? [];

        return collect($this->defaultPolicies())->mapWithKeys(function (array $default, string $key) use ($policies): array {
            return [$key => $this->normalizePolicy($key, array_replace($default, $policies[$key] ?? []))];
        })->all();
    }

    /** @return array<string, mixed> */
    public function policy(string $key): array
    {
        return $this->policies()[$key] ?? $this->normalizePolicy($key, $this->defaultPolicies()['api_requests']);
    }

    /** @param array<string, array<string, mixed>> $policies */
    public function putPolicies(array $policies, User $actor): SecuritySetting
    {
        $normalized = collect($this->defaultPolicies())->mapWithKeys(function (array $default, string $key) use ($policies): array {
            return [$key => $this->normalizePolicy($key, array_replace($default, $policies[$key] ?? []))];
        })->all();

        return $this->put('rate_limits', ['policies' => $normalized], $actor);
    }

    /** @return array{allowlist: array<int, string>, blocklist: array<int, string>, temporary_block_ready: bool} */
    public function ipAccess(): array
    {
        return array_replace($this->defaultIpAccess(), $this->payload('ip_access'));
    }

    /** @param array<string, mixed> $payload */
    public function putIpAccess(array $payload, User $actor): SecuritySetting
    {
        return $this->put('ip_access', [
            'allowlist' => $this->cleanIpList($payload['allowlist'] ?? []),
            'blocklist' => $this->cleanIpList($payload['blocklist'] ?? []),
            'temporary_block_ready' => (bool) ($payload['temporary_block_ready'] ?? false),
        ], $actor);
    }

    /** @return array<string, mixed> */
    public function adminAccess(): array
    {
        return array_replace($this->defaultAdminAccess(), $this->payload('admin_access'));
    }

    /** @param array<string, mixed> $payload */
    public function putAdminAccess(array $payload, User $actor): SecuritySetting
    {
        return $this->put('admin_access', [
            'password_min_length' => max(8, (int) ($payload['password_min_length'] ?? 12)),
            'password_letters' => (bool) ($payload['password_letters'] ?? true),
            'password_numbers' => (bool) ($payload['password_numbers'] ?? true),
            'password_symbols' => (bool) ($payload['password_symbols'] ?? true),
            'require_email_verification' => (bool) ($payload['require_email_verification'] ?? false),
            'admin_session_lifetime' => max(15, (int) ($payload['admin_session_lifetime'] ?? 120)),
            'login_alerts' => (bool) ($payload['login_alerts'] ?? true),
            'admin_ip_allowlist_ready' => (bool) ($payload['admin_ip_allowlist_ready'] ?? false),
            'require_2fa_readiness' => (bool) ($payload['require_2fa_readiness'] ?? false),
            'critical_notifications_ready' => (bool) ($payload['critical_notifications_ready'] ?? true),
        ], $actor);
    }

    /** @return array<string, array<string, mixed>> */
    public function defaultPolicies(): array
    {
        return [
            'login' => ['label' => 'Login attempts', 'max_attempts' => 5, 'window_minutes' => 1, 'cooldown_minutes' => 1, 'strategy' => 'per_ip', 'is_active' => true],
            'register' => ['label' => 'Registration attempts', 'max_attempts' => 3, 'window_minutes' => 10, 'cooldown_minutes' => 10, 'strategy' => 'per_ip', 'is_active' => true],
            'forgot_password' => ['label' => 'Forgot password', 'max_attempts' => 3, 'window_minutes' => 10, 'cooldown_minutes' => 10, 'strategy' => 'per_ip', 'is_active' => true],
            'mailbox_creation' => ['label' => 'Mailbox creation', 'max_attempts' => 10, 'window_minutes' => 1, 'cooldown_minutes' => 1, 'strategy' => 'per_session', 'is_active' => true],
            'inbox_refresh' => ['label' => 'Inbox refresh', 'max_attempts' => 60, 'window_minutes' => 1, 'cooldown_minutes' => 1, 'strategy' => 'per_session', 'is_active' => true],
            'comments' => ['label' => 'Comment submission', 'max_attempts' => 3, 'window_minutes' => 5, 'cooldown_minutes' => 5, 'strategy' => 'per_ip', 'is_active' => true],
            'contact_form' => ['label' => 'Contact form', 'max_attempts' => 3, 'window_minutes' => 5, 'cooldown_minutes' => 5, 'strategy' => 'per_ip', 'is_active' => true],
            'api_requests' => ['label' => 'API requests', 'max_attempts' => 60, 'window_minutes' => 1, 'cooldown_minutes' => 1, 'strategy' => 'per_user', 'is_active' => false],
        ];
    }

    /** @return array<string, string> */
    public function strategies(): array
    {
        return [
            'per_ip' => 'Per IP',
            'per_user' => 'Per user',
            'per_session' => 'Per session',
        ];
    }

    private function put(string $group, array $payload, User $actor): SecuritySetting
    {
        return SecuritySetting::query()->updateOrCreate(
            ['group' => $group],
            [
                'payload' => $payload,
                'encrypted_secrets' => null,
                'test_history' => [],
                'updated_by' => $actor->id,
            ],
        );
    }

    /** @return array<string, mixed> */
    private function payload(string $group): array
    {
        if (! $this->tableIsReady()) {
            return [];
        }

        return SecuritySetting::query()->where('group', $group)->value('payload') ?? [];
    }

    private function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('security_settings');
        } catch (Throwable) {
            return false;
        }
    }

    /** @param array<string, mixed> $policy */
    private function normalizePolicy(string $key, array $policy): array
    {
        $strategy = in_array($policy['strategy'] ?? null, array_keys($this->strategies()), true)
            ? $policy['strategy']
            : 'per_ip';

        return [
            'key' => $key,
            'label' => (string) ($policy['label'] ?? str($key)->headline()),
            'max_attempts' => max(1, min(10000, (int) ($policy['max_attempts'] ?? 1))),
            'window_minutes' => max(1, min(1440, (int) ($policy['window_minutes'] ?? 1))),
            'cooldown_minutes' => max(1, min(1440, (int) ($policy['cooldown_minutes'] ?? 1))),
            'strategy' => $strategy,
            'is_active' => (bool) ($policy['is_active'] ?? true),
        ];
    }

    /** @param array<int, string>|string $value */
    private function cleanIpList(array|string $value): array
    {
        $items = is_array($value) ? $value : preg_split('/\R/', $value);

        return collect($items)
            ->map(fn (?string $ip): string => trim((string) $ip))
            ->filter(fn (string $ip): bool => $ip !== '')
            ->unique()
            ->values()
            ->all();
    }

    /** @return array{allowlist: array<int, string>, blocklist: array<int, string>, temporary_block_ready: bool} */
    private function defaultIpAccess(): array
    {
        return ['allowlist' => [], 'blocklist' => [], 'temporary_block_ready' => false];
    }

    /** @return array<string, mixed> */
    private function defaultAdminAccess(): array
    {
        return [
            'password_min_length' => 12,
            'password_letters' => true,
            'password_numbers' => true,
            'password_symbols' => true,
            'require_email_verification' => false,
            'admin_session_lifetime' => (int) config('session.lifetime', 120),
            'login_alerts' => true,
            'admin_ip_allowlist_ready' => false,
            'require_2fa_readiness' => false,
            'critical_notifications_ready' => true,
        ];
    }
}
