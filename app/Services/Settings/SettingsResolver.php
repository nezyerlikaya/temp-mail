<?php

namespace App\Services\Settings;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingsResolver
{
    public function __construct(private readonly SettingsStore $store) {}

    /** @return array<string, mixed> */
    public function group(string $group): array
    {
        return array_replace_recursive($this->defaults()[$group] ?? [], $this->store->group($group));
    }

    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        return collect(array_keys($this->defaults()))->mapWithKeys(fn (string $group): array => [
            $group => $this->group($group),
        ])->all();
    }

    /** @return array<string, array<string, mixed>> */
    public function defaults(): array
    {
        return [
            'general' => [
                'site_name' => config('app.name', 'Temp Mail Cloud'),
                'site_tagline' => 'Private inboxes. Clear control.',
                'admin_email' => 'admin@example.com',
                'support_email' => 'support@example.com',
                'abuse_email' => 'abuse@example.com',
                'default_language' => 'en',
                'default_timezone' => 'UTC',
                'date_format' => 'M j, Y',
                'time_format' => 'H:i',
            ],
            'brand' => [
                'logo_media_id' => null,
                'favicon_media_id' => null,
                'app_icon_media_id' => null,
                'public_site_name' => config('app.name', 'Temp Mail Cloud'),
                'footer_brand_text' => 'Temp Mail Cloud',
            ],
            'localization' => [
                'default_locale' => config('app.locale', 'en'),
                'fallback_locale' => config('app.fallback_locale', 'en'),
                'rtl_auto_detection' => true,
                'missing_locale_behavior' => 'fallback',
            ],
            'maintenance' => [
                'enabled' => false,
                'message' => 'We are completing scheduled maintenance. Please try again shortly.',
                'allowed_admin_ips' => [],
            ],
            'legal' => [
                'privacy_page_id' => null,
                'terms_page_id' => null,
                'cookie_page_id' => null,
                'abuse_page_id' => null,
                'dmca_page_id' => null,
                'contact_page_id' => null,
            ],
        ];
    }

    /** @return array<string, string> */
    public function activeLanguages(): array
    {
        if (Schema::hasTable('locales') && Schema::hasColumns('locales', ['code', 'name', 'active'])) {
            return DB::table('locales')
                ->where('active', true)
                ->orderBy('name')
                ->pluck('name', 'code')
                ->all();
        }

        return ['en' => 'English', 'tr' => 'Turkish', 'de' => 'German', 'fr' => 'French', 'es' => 'Spanish'];
    }

    public function applyRuntime(): void
    {
        $general = $this->group('general');
        $localization = $this->group('localization');

        config([
            'app.name' => $general['site_name'],
            'app.timezone' => $general['default_timezone'],
            'app.locale' => $localization['default_locale'],
            'app.fallback_locale' => $localization['fallback_locale'],
        ]);

        app()->setLocale($localization['default_locale']);
        date_default_timezone_set($general['default_timezone']);
    }
}
