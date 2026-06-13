<?php

namespace App\Services\Api;

use App\Models\ApiSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ApiSettingsStore
{
    private const CACHE_KEY = 'api-access.settings';

    /** @return array<string, mixed> */
    public function get(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $payload = ApiSetting::query()->where('group', 'access')->value('payload') ?? [];

            return [...$this->defaults(), ...$payload];
        });
    }

    /** @param array<string, mixed> $payload */
    public function put(array $payload, User $actor): array
    {
        $settings = [
            'api_enabled' => (bool) ($payload['api_enabled'] ?? false),
            'free_api_enabled' => (bool) ($payload['free_api_enabled'] ?? false),
            'premium_api_enabled' => (bool) ($payload['premium_api_enabled'] ?? false),
            'business_api_enabled' => (bool) ($payload['business_api_enabled'] ?? true),
        ];

        ApiSetting::query()->updateOrCreate(
            ['group' => 'access'],
            ['payload' => $settings, 'updated_by' => $actor->id],
        );

        Cache::forget(self::CACHE_KEY);

        return $settings;
    }

    /** @return array<string, mixed> */
    private function defaults(): array
    {
        return [
            'api_enabled' => false,
            'free_api_enabled' => false,
            'premium_api_enabled' => false,
            'business_api_enabled' => true,
        ];
    }
}
