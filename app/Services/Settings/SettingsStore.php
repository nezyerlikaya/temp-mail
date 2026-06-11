<?php

namespace App\Services\Settings;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class SettingsStore
{
    private const CACHE_KEY = 'system-settings.groups';

    /** @return array<string, mixed> */
    public function group(string $group): array
    {
        return $this->all()[$group] ?? [];
    }

    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => SystemSetting::query()
            ->get()
            ->mapWithKeys(fn (SystemSetting $setting): array => [$setting->group => $setting->payload])
            ->all());
    }

    /** @param array<string, mixed> $payload */
    public function put(string $group, array $payload, User $actor): void
    {
        $this->assertSafePayload($payload);

        SystemSetting::query()->updateOrCreate(
            ['group' => $group],
            ['payload' => $payload, 'updated_by' => $actor->id],
        );

        Cache::forget(self::CACHE_KEY);
    }

    public function forget(string $group): void
    {
        SystemSetting::query()->where('group', $group)->delete();
        Cache::forget(self::CACHE_KEY);
    }

    /** @param array<string, mixed> $payload */
    private function assertSafePayload(array $payload, string $prefix = ''): void
    {
        foreach ($payload as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (preg_match('/(?:secret|password|token|api[_-]?key|private[_-]?key)/i', (string) $key)) {
                throw new InvalidArgumentException("Secret setting [{$path}] is not allowed.");
            }

            if (is_array($value)) {
                $this->assertSafePayload($value, $path);
            }
        }
    }
}
