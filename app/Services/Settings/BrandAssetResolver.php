<?php

namespace App\Services\Settings;

class BrandAssetResolver
{
    public function __construct(private readonly SettingsResolver $settings) {}

    /** @return array<string, array{media_id: int|null, connected: bool, label: string, fallback: string}> */
    public function assets(): array
    {
        $brand = $this->settings->group('brand');

        return [
            'logo' => $this->asset($brand['logo_media_id'], 'Logo', 'TM wordmark'),
            'favicon' => $this->asset($brand['favicon_media_id'], 'Favicon', 'TM initials'),
            'app_icon' => $this->asset($brand['app_icon_media_id'], 'App icon', 'TM initials'),
        ];
    }

    /** @return array{media_id: int|null, connected: bool, label: string, fallback: string} */
    private function asset(?int $mediaId, string $label, string $fallback): array
    {
        return ['media_id' => $mediaId, 'connected' => $mediaId !== null, 'label' => $label, 'fallback' => $fallback];
    }
}
