<?php

namespace App\Services\Integrations;

class IntegrationFieldRegistry
{
    /** @return array<string, array<int, array<string, mixed>>> */
    public function all(): array
    {
        return [
            'mailgun' => [$this->secret('api_key', 'API key'), $this->text('domain', 'Sending domain'), $this->email('sender_email', 'Sender email')],
            'postmark' => [$this->secret('server_token', 'Server token'), $this->email('sender_email', 'Sender email')],
            'amazon_ses' => [$this->text('access_key_id', 'Access key ID'), $this->secret('secret_access_key', 'Secret access key'), $this->select('region', 'Region', ['us-east-1', 'us-west-2', 'eu-west-1', 'eu-central-1'])],
            'custom_smtp' => [$this->text('host', 'SMTP host'), $this->text('port', 'Port'), $this->text('username', 'Username'), $this->secret('password', 'Password')],
            'stripe' => [$this->text('publishable_key', 'Publishable key'), $this->secret('secret_key', 'Secret key')],
            'paddle' => [$this->text('vendor_id', 'Vendor ID'), $this->secret('api_key', 'API key')],
            'iyzico' => [$this->secret('api_key', 'API key'), $this->secret('secret_key', 'Secret key'), $this->url('base_url', 'Base URL')],
            'google_analytics' => [$this->text('measurement_id', 'Measurement ID'), $this->boolean('enabled_ip_anonymization', 'IP anonymization')],
            'plausible' => [$this->text('domain', 'Domain'), $this->url('script_url', 'Script URL')],
            'umami' => [$this->text('website_id', 'Website ID'), $this->url('host_url', 'Host URL')],
            'sentry' => [$this->url('dsn', 'DSN')],
            'uptimerobot' => [$this->secret('api_key', 'API key')],
            'better_stack' => [$this->url('heartbeat_url', 'Heartbeat URL'), $this->secret('api_token', 'API token')],
            'google_search_console' => [$this->url('site_url', 'Site URL'), $this->secret('service_account_json', 'Service account JSON', reveal: false)],
            'bing_webmaster' => [$this->url('site_url', 'Site URL'), $this->secret('api_key', 'API key')],
            'webhooks' => [$this->url('endpoint_url', 'Endpoint URL'), $this->secret('signing_secret', 'Signing secret')],
            'zapier_make' => [$this->url('callback_url', 'Callback URL'), $this->secret('shared_secret', 'Shared secret')],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function fields(string $key): array
    {
        return $this->all()[$key] ?? [];
    }

    /** @return array<int, string> */
    public function secretKeys(string $key): array
    {
        return collect($this->fields($key))->where('type', 'secret')->pluck('key')->all();
    }

    /** @return array<int, string> */
    public function payloadKeys(string $key): array
    {
        return collect($this->fields($key))->reject(fn (array $field): bool => $field['type'] === 'secret')->pluck('key')->all();
    }

    private function text(string $key, string $label): array
    {
        return compact('key', 'label') + ['type' => 'text', 'required' => true];
    }

    private function url(string $key, string $label): array
    {
        return compact('key', 'label') + ['type' => 'url', 'required' => true];
    }

    private function email(string $key, string $label): array
    {
        return compact('key', 'label') + ['type' => 'email', 'required' => false];
    }

    /** @param array<int, string> $options */
    private function select(string $key, string $label, array $options): array
    {
        return compact('key', 'label', 'options') + ['type' => 'select', 'required' => true];
    }

    private function boolean(string $key, string $label): array
    {
        return compact('key', 'label') + ['type' => 'boolean', 'required' => false];
    }

    private function secret(string $key, string $label, bool $reveal = true): array
    {
        return compact('key', 'label', 'reveal') + ['type' => 'secret', 'required' => true];
    }
}
