<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Crypt;
use Throwable;

class IntegrationSecretStore
{
    /** @param array<string, mixed> $secrets */
    public function encrypt(array $secrets): ?string
    {
        $filtered = collect($secrets)
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->all();

        return $filtered === [] ? null : Crypt::encryptString(json_encode($filtered, JSON_THROW_ON_ERROR));
    }

    /** @return array<string, string> */
    public function decrypt(?string $encrypted): array
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

    /** @param array<int, string> $secretKeys */
    public function masked(?string $encrypted, array $secretKeys): array
    {
        $secrets = $this->decrypt($encrypted);

        return collect($secretKeys)->mapWithKeys(fn (string $key): array => [
            $key => filled($secrets[$key] ?? null) ? $this->mask((string) $secrets[$key]) : null,
        ])->all();
    }

    public function mask(string $value): string
    {
        $length = max(8, min(12, strlen($value)));

        return str_repeat('*', $length);
    }
}
