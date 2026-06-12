<?php

namespace App\Services\Security;

use App\Models\SecuritySetting;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SecuritySettingsStore
{
    public function bot(): array
    {
        return $this->group('bot_protection');
    }

    public function akismet(): array
    {
        return $this->group('akismet');
    }

    /** @return array<string, mixed> */
    public function group(string $group, bool $withSecrets = false): array
    {
        $defaults = $this->defaults()[$group] ?? [];
        $setting = $this->setting($group);
        $payload = array_replace_recursive($defaults['payload'] ?? [], $setting?->payload ?? []);
        $secrets = $withSecrets ? $this->decryptSecrets($setting?->encrypted_secrets) : [];

        return [
            ...$payload,
            'secrets' => $withSecrets ? $secrets : $this->maskedSecrets($defaults['secrets'] ?? [], $setting),
            'last_tested_at' => $setting?->last_tested_at,
            'last_test_status' => $setting?->last_test_status,
            'test_history' => $setting?->test_history ?? [],
        ];
    }

    /** @param array<string, mixed> $payload */
    public function put(string $group, array $payload, array $secrets, User $actor): SecuritySetting
    {
        $existing = $this->setting($group);
        $mergedSecrets = array_filter([
            ...$this->decryptSecrets($existing?->encrypted_secrets),
            ...array_filter($secrets, fn (?string $value): bool => filled($value)),
        ], fn (mixed $value): bool => filled($value));

        return SecuritySetting::query()->updateOrCreate(
            ['group' => $group],
            [
                'payload' => $payload,
                'encrypted_secrets' => $mergedSecrets === [] ? $existing?->encrypted_secrets : Crypt::encryptString(json_encode($mergedSecrets, JSON_THROW_ON_ERROR)),
                'test_history' => $existing?->test_history ?? [],
                'last_tested_at' => $existing?->last_tested_at,
                'last_test_status' => $existing?->last_test_status,
                'updated_by' => $actor->id,
            ],
        );
    }

    /** @param array{status: string, message: string} $result */
    public function recordTest(string $group, array $result): void
    {
        $setting = $this->setting($group) ?? SecuritySetting::query()->create([
            'group' => $group,
            'payload' => $this->defaults()[$group]['payload'] ?? [],
            'encrypted_secrets' => null,
            'test_history' => [],
        ]);

        $history = collect($setting->test_history ?? [])
            ->prepend([
                'status' => $result['status'],
                'message' => $result['message'],
                'tested_at' => now()->toIso8601String(),
            ])
            ->take(5)
            ->values()
            ->all();

        $setting->forceFill([
            'test_history' => $history,
            'last_tested_at' => now(),
            'last_test_status' => $result['status'],
        ])->save();
    }

    public function secret(string $group, string $field): ?string
    {
        return $this->decryptSecrets($this->setting($group)?->encrypted_secrets)[$field] ?? null;
    }

    public function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('security_settings');
        } catch (Throwable) {
            return false;
        }
    }

    /** @return array<string, array<string, mixed>> */
    public function defaults(): array
    {
        return [
            'bot_protection' => [
                'payload' => [
                    'provider' => 'turnstile',
                    'recaptcha_mode' => 'v2_checkbox',
                    'minimum_score' => 0.5,
                    'fail_mode' => 'challenge',
                    'is_active' => false,
                    'protected_forms' => ['login', 'register', 'forgot_password', 'comments'],
                ],
                'secrets' => ['site_key' => null, 'secret_key' => null],
            ],
            'akismet' => [
                'payload' => [
                    'site_url' => config('app.url'),
                    'is_active' => false,
                    'protected_comments' => true,
                    'contact_form_readiness' => false,
                    'mode' => 'hold_suspicious',
                ],
                'secrets' => ['api_key' => null],
            ],
        ];
    }

    private function setting(string $group): ?SecuritySetting
    {
        return $this->tableIsReady() ? SecuritySetting::query()->where('group', $group)->first() : null;
    }

    /** @return array<string, string> */
    private function decryptSecrets(?string $encrypted): array
    {
        if (! filled($encrypted)) {
            return [];
        }

        try {
            return json_decode(Crypt::decryptString($encrypted), true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return [];
        }
    }

    /** @param array<string, mixed> $defaults */
    private function maskedSecrets(array $defaults, ?SecuritySetting $setting): array
    {
        $secrets = $this->decryptSecrets($setting?->encrypted_secrets);

        return collect($defaults)->mapWithKeys(fn (mixed $default, string $key): array => [
            $key => filled($secrets[$key] ?? null) ? $this->mask((string) $secrets[$key]) : null,
        ])->all();
    }

    private function mask(string $value): string
    {
        return str_repeat('•', max(8, min(12, strlen($value))));
    }
}
