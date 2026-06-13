<?php

namespace App\Services\PublicSite;

use App\Services\Settings\BrandAssetResolver;
use App\Services\Settings\SettingsResolver;
use Throwable;

class PublicBrandResolver
{
    public function __construct(
        private readonly SettingsResolver $settings,
        private readonly BrandAssetResolver $assets,
    ) {}

    /** @return array<string, mixed> */
    public function resolve(): array
    {
        try {
            $brand = $this->settings->group('brand');
            $general = $this->settings->group('general');
            $assets = $this->assets->assets();

            return [
                'name' => $brand['public_site_name'] ?: $general['site_name'],
                'tagline' => $general['site_tagline'] ?? null,
                'footer_text' => $brand['footer_brand_text'] ?: $brand['public_site_name'],
                'logo' => $assets['logo']['selected'] ?? null,
                'favicon' => $assets['favicon']['selected'] ?? null,
            ];
        } catch (Throwable) {
            return [
                'name' => config('app.name', 'Temp Mail Cloud'),
                'tagline' => 'Private inboxes. Clear control.',
                'footer_text' => config('app.name', 'Temp Mail Cloud'),
                'logo' => null,
                'favicon' => null,
            ];
        }
    }
}
