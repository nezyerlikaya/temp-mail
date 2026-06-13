<?php

namespace App\Services\Integrations;

use App\Models\IntegrationSetting;
use App\Models\User;

class IntegrationEventLogger
{
    /** @param array<string, mixed> $result */
    public function record(IntegrationSetting $setting, array $result, User $actor, int $durationMs): IntegrationSetting
    {
        $event = [
            'status' => $this->status((string) $result['status']),
            'tested_at' => now()->toISOString(),
            'duration_ms' => max(0, $durationMs),
            'error_code' => $this->safeNullable($result['error_code'] ?? null),
            'message' => $this->safeMessage((string) ($result['message'] ?? 'Connection test completed.')),
            'environment' => $setting->environment,
            'provider_state' => $this->safeMessage((string) ($result['provider_state'] ?? 'readiness_only')),
        ];

        $history = collect($setting->test_history ?? [])
            ->prepend($event)
            ->take(5)
            ->values()
            ->all();

        $setting->forceFill([
            'connection_status' => $event['status'],
            'test_history' => $history,
            'last_tested_at' => now(),
            'updated_by' => $actor->id,
        ])->save();

        return $setting->refresh();
    }

    /** @return array<string, mixed> */
    public function safeAuditPayload(IntegrationSetting $setting, array $result, int $durationMs): array
    {
        return [
            'integration_key' => $setting->integration_key,
            'environment' => $setting->environment,
            'status' => $this->status((string) ($result['status'] ?? 'failed')),
            'error_code' => $this->safeNullable($result['error_code'] ?? null),
            'duration_ms' => max(0, $durationMs),
        ];
    }

    private function status(string $status): string
    {
        return in_array($status, ['not_tested', 'connected', 'degraded', 'failed', 'disabled'], true) ? $status : 'failed';
    }

    private function safeNullable(mixed $value): ?string
    {
        $clean = trim($this->safeMessage((string) $value));

        return $clean === '' ? null : $clean;
    }

    private function safeMessage(string $message): string
    {
        $message = preg_replace('/(sk|pk|rk|key|token|secret|password)_[A-Za-z0-9_\-]+/i', '[redacted]', $message) ?? $message;
        $message = preg_replace('/[A-Za-z0-9+\/=_-]{32,}/', '[redacted]', $message) ?? $message;

        return str($message)->limit(240, '...')->toString();
    }
}
