<?php

namespace App\Services\Audit;

class AuditSanitizer
{
    /** @var array<int, string> */
    private const SENSITIVE_KEYS = [
        'password',
        'secret',
        'token',
        'api_key',
        'smtp_password',
        'database_password',
    ];

    /** @param array<string, mixed> $metadata */
    public function sanitize(array $metadata): array
    {
        return collect($metadata)
            ->mapWithKeys(fn (mixed $value, string $key): array => [
                $key => $this->isSensitive($key) ? $this->maskedValue($value) : $this->sanitizeValue($value),
            ])
            ->all();
    }

    /** @return array<int, string> */
    public function sensitiveKeys(): array
    {
        return self::SENSITIVE_KEYS;
    }

    private function sanitizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $this->sanitize($value);
        }

        return $value;
    }

    private function maskedValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn (): string => '[masked]')
                ->all();
        }

        return '[masked]';
    }

    private function isSensitive(string $key): bool
    {
        $normalized = str($key)->lower()->replace(['-', ' '], '_')->toString();

        return collect(self::SENSITIVE_KEYS)->contains(fn (string $sensitive): bool => $normalized === $sensitive
            || str_contains($normalized, $sensitive));
    }
}
