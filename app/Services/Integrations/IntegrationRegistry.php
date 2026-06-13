<?php

namespace App\Services\Integrations;

use Illuminate\Support\Collection;

class IntegrationRegistry
{
    /** @return array<string, string> */
    public function categories(): array
    {
        return [
            'email_delivery' => 'Email Delivery',
            'payments' => 'Payments',
            'analytics' => 'Analytics',
            'monitoring' => 'Monitoring',
            'search_seo' => 'Search and SEO',
            'automation' => 'Automation',
        ];
    }

    /** @return array<string, string> */
    public function environments(): array
    {
        return ['sandbox' => 'Sandbox / test', 'production' => 'Production'];
    }

    /** @return Collection<int, array<string, mixed>> */
    public function integrations(): Collection
    {
        return collect([
            $this->integration('mailgun', 'Mailgun', 'email_delivery', 'Transactional email delivery readiness.', 'Email Templates and Notifications', ['api_key', 'domain']),
            $this->integration('postmark', 'Postmark', 'email_delivery', 'Postmark server token and sender readiness.', 'Email Templates and Notifications', ['server_token', 'sender_email']),
            $this->integration('amazon_ses', 'Amazon SES', 'email_delivery', 'SES key and region readiness for email delivery.', 'Email Templates and Notifications', ['access_key_id', 'secret_access_key', 'region']),
            $this->integration('custom_smtp', 'Custom SMTP', 'email_delivery', 'External SMTP service readiness reference.', 'Email Templates and Notifications', ['host', 'port', 'username', 'password']),
            $this->integration('stripe', 'Stripe', 'payments', 'Payment provider configuration readiness. Checkout is implemented later.', 'Plans & Memberships', ['publishable_key', 'secret_key']),
            $this->integration('paddle', 'Paddle', 'payments', 'Paddle seller and API readiness. Checkout is implemented later.', 'Plans & Memberships', ['vendor_id', 'api_key']),
            $this->integration('iyzico', 'Iyzico readiness', 'payments', 'Iyzico sandbox and production credential readiness.', 'Plans & Memberships', ['api_key', 'secret_key', 'base_url']),
            $this->integration('google_analytics', 'Google Analytics', 'analytics', 'Privacy-aware analytics configuration reference.', 'Product Analytics', ['measurement_id']),
            $this->integration('plausible', 'Plausible', 'analytics', 'Plausible domain and endpoint readiness.', 'Product Analytics', ['domain', 'script_url']),
            $this->integration('umami', 'Umami', 'analytics', 'Umami website id and host readiness.', 'Product Analytics', ['website_id', 'host_url']),
            $this->integration('sentry', 'Sentry', 'monitoring', 'Sentry DSN readiness for server-side error monitoring.', 'Backups & Health', ['dsn']),
            $this->integration('uptimerobot', 'UptimeRobot', 'monitoring', 'UptimeRobot API key readiness for uptime checks.', 'Backups & Health', ['api_key']),
            $this->integration('better_stack', 'Better Stack', 'monitoring', 'Better Stack heartbeat and status monitoring readiness.', 'Backups & Health', ['heartbeat_url', 'api_token']),
            $this->integration('google_search_console', 'Google Search Console', 'search_seo', 'Search Console ownership readiness for SEO workflows.', 'SEO Growth Center', ['site_url', 'service_account_json']),
            $this->integration('bing_webmaster', 'Bing Webmaster', 'search_seo', 'Bing Webmaster API key and site readiness.', 'SEO Growth Center', ['site_url', 'api_key']),
            $this->integration('webhooks', 'Webhooks', 'automation', 'Outbound webhook endpoint readiness for automation.', 'API Access', ['endpoint_url', 'signing_secret']),
            $this->integration('zapier_make', 'Zapier/Make readiness', 'automation', 'Automation platform readiness metadata. App publishing comes later.', 'API Access', ['callback_url', 'shared_secret']),
        ]);
    }

    /** @return array<string, mixed>|null */
    public function find(string $key): ?array
    {
        return $this->integrations()->firstWhere('key', $key);
    }

    /** @return array<int, string> */
    public function keys(): array
    {
        return $this->integrations()->pluck('key')->all();
    }

    /** @param array<int, string> $required */
    private function integration(string $key, string $name, string $category, string $description, string $owner, array $required): array
    {
        return compact('key', 'name', 'category', 'description', 'owner', 'required');
    }
}
