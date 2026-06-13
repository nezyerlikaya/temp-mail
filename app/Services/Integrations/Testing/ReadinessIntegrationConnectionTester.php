<?php

namespace App\Services\Integrations\Testing;

use App\Models\IntegrationSetting;
use App\Services\Integrations\Contracts\IntegrationConnectionTester;

class ReadinessIntegrationConnectionTester implements IntegrationConnectionTester
{
    /** @return array{status: string, error_code: string|null, message: string, provider_state: string} */
    public function test(array $definition, IntegrationSetting $setting, array $payload, array $secrets): array
    {
        if (! $setting->is_active) {
            return $this->result('disabled', 'integration_disabled', 'Activate this integration before running a production readiness check.', 'unavailable');
        }

        $missing = collect($definition['required'])
            ->reject(fn (string $key): bool => filled($payload[$key] ?? null) || filled($secrets[$key] ?? null))
            ->values();

        if ($missing->isNotEmpty()) {
            return $this->result('failed', 'missing_configuration', 'Required configuration is incomplete. Add the missing fields and retry the test.', 'unavailable');
        }

        return match ($definition['key']) {
            'custom_smtp' => $this->smtp($payload),
            'stripe' => $this->prefixedKeys($payload, $secrets, 'pk_', 'sk_', 'stripe_credentials_ready'),
            'webhooks', 'zapier_make' => $this->webhook($payload),
            'sentry' => $this->httpsUrl($payload['dsn'] ?? null, 'sentry_dsn_ready'),
            'plausible' => $this->httpsUrl($payload['script_url'] ?? null, 'plausible_script_ready'),
            'umami' => $this->httpsUrl($payload['host_url'] ?? null, 'umami_host_ready'),
            'iyzico' => $this->httpsUrl($payload['base_url'] ?? null, 'iyzico_endpoint_ready'),
            'google_analytics' => $this->measurementId($payload),
            'google_search_console' => $this->searchConsole($payload, $secrets),
            'bing_webmaster' => $this->httpsUrl($payload['site_url'] ?? null, 'bing_site_ready'),
            default => $this->result('connected', null, 'Credential shape and required configuration are ready. No destructive provider action was performed.', 'readiness_only'),
        };
    }

    /** @return array{status: string, error_code: string|null, message: string, provider_state: string} */
    private function smtp(array $payload): array
    {
        $port = (int) ($payload['port'] ?? 0);

        if ($port < 1 || $port > 65535) {
            return $this->result('failed', 'invalid_smtp_port', 'SMTP port must be between 1 and 65535.', 'unavailable');
        }

        return $this->result('degraded', 'smtp_probe_not_run', 'SMTP configuration is complete. A non-sending readiness check was recorded without opening a mail session.', 'manual_probe_ready');
    }

    /** @return array{status: string, error_code: string|null, message: string, provider_state: string} */
    private function prefixedKeys(array $payload, array $secrets, string $publicPrefix, string $secretPrefix, string $code): array
    {
        if (! str_starts_with((string) ($payload['publishable_key'] ?? ''), $publicPrefix) || ! str_starts_with((string) ($secrets['secret_key'] ?? ''), $secretPrefix)) {
            return $this->result('degraded', 'credential_shape_warning', 'Required credentials are present, but their key format does not match the expected provider prefix.', 'readiness_only');
        }

        return $this->result('connected', $code, 'Credential format is valid for readiness. No payment, checkout, or billable action was performed.', 'readiness_only');
    }

    /** @return array{status: string, error_code: string|null, message: string, provider_state: string} */
    private function webhook(array $payload): array
    {
        $endpoint = (string) ($payload['endpoint_url'] ?? $payload['callback_url'] ?? '');

        if (! str_starts_with($endpoint, 'https://')) {
            return $this->result('degraded', 'webhook_https_required', 'Webhook endpoints should use HTTPS before production activation.', 'readiness_only');
        }

        return $this->result('connected', null, 'Webhook endpoint and signing secret are ready. No outbound request was sent.', 'readiness_only');
    }

    /** @return array{status: string, error_code: string|null, message: string, provider_state: string} */
    private function httpsUrl(mixed $url, string $code): array
    {
        if (! str_starts_with((string) $url, 'https://')) {
            return $this->result('degraded', 'https_recommended', 'Provider URL is present, but HTTPS is recommended for production readiness.', 'readiness_only');
        }

        return $this->result('connected', $code, 'Provider endpoint format is ready. No remote synchronization was performed.', 'readiness_only');
    }

    /** @return array{status: string, error_code: string|null, message: string, provider_state: string} */
    private function measurementId(array $payload): array
    {
        if (! preg_match('/^G-[A-Z0-9]+$/', (string) ($payload['measurement_id'] ?? ''))) {
            return $this->result('degraded', 'measurement_id_shape_warning', 'Measurement ID is present, but it does not match the expected GA4 format.', 'readiness_only');
        }

        return $this->result('connected', null, 'Analytics measurement configuration is ready. No visitor data import was performed.', 'readiness_only');
    }

    /** @return array{status: string, error_code: string|null, message: string, provider_state: string} */
    private function searchConsole(array $payload, array $secrets): array
    {
        if (! str_starts_with((string) ($payload['site_url'] ?? ''), 'https://')) {
            return $this->result('degraded', 'site_https_recommended', 'Search property is present, but HTTPS is recommended for production readiness.', 'readiness_only');
        }

        json_decode((string) ($secrets['service_account_json'] ?? ''), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->result('failed', 'invalid_service_account_json', 'Service account JSON could not be parsed. Replace it and retry the test.', 'unavailable');
        }

        return $this->result('connected', null, 'Search Console ownership credential shape is ready. No search data ingestion was performed.', 'readiness_only');
    }

    /** @return array{status: string, error_code: string|null, message: string, provider_state: string} */
    private function result(string $status, ?string $errorCode, string $message, string $providerState): array
    {
        return [
            'status' => $status,
            'error_code' => $errorCode,
            'message' => $message,
            'provider_state' => $providerState,
        ];
    }
}
