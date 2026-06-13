<?php

namespace App\Services\Analytics;

use Illuminate\Http\Request;

class AnalyticsPrivacySanitizer
{
    /** @var array<int, string> */
    private const ALLOWED_METADATA_KEYS = [
        'source',
        'mailbox_type',
        'owner_assigned',
        'locale_assigned',
        'plan_key',
        'status',
        'environment',
        'method',
        'route',
        'response_status',
        'domain_status',
        'comment_status',
    ];

    /** @var array<int, string> */
    private const SENSITIVE_FRAGMENTS = [
        'authorization',
        'body',
        'content',
        'email',
        'header',
        'ip',
        'message',
        'password',
        'raw',
        'recipient',
        'secret',
        'sender',
        'token',
    ];

    /** @param array<string, mixed> $metadata @return array<string, mixed> */
    public function metadata(array $metadata): array
    {
        return collect($metadata)
            ->filter(fn (mixed $value, string $key): bool => $this->isAllowed($key))
            ->map(fn (mixed $value): mixed => $this->safeValue($value))
            ->all();
    }

    public function hashIp(?string $ip): ?string
    {
        if (! filled($ip)) {
            return null;
        }

        return hash_hmac('sha256', (string) $ip, (string) config('app.key'));
    }

    public function visitorHash(?string $value, ?Request $request = null): ?string
    {
        $source = $value ?: $request?->cookie('tm_visitor') ?: $request?->userAgent();

        return filled($source) ? hash_hmac('sha256', (string) $source, (string) config('app.key')) : null;
    }

    public function sessionHash(?string $value, ?Request $request = null): ?string
    {
        $source = $value ?: $request?->session()?->getId();

        return filled($source) ? hash_hmac('sha256', (string) $source, (string) config('app.key')) : null;
    }

    private function isAllowed(string $key): bool
    {
        $normalized = str($key)->lower()->replace(['-', ' '], '_')->toString();

        if (! in_array($normalized, self::ALLOWED_METADATA_KEYS, true)) {
            return false;
        }

        return ! collect(self::SENSITIVE_FRAGMENTS)->contains(fn (string $fragment): bool => str_contains($normalized, $fragment));
    }

    private function safeValue(mixed $value): mixed
    {
        if (is_bool($value) || is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return round($value, 4);
        }

        return str(strip_tags((string) $value))->limit(120)->toString();
    }
}
