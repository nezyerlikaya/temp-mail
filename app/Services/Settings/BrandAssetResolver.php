<?php

namespace App\Services\Settings;

use App\Models\MediaAsset;
use App\Services\Media\MediaPickerSearchService;

class BrandAssetResolver
{
    public function __construct(
        private readonly SettingsResolver $settings,
        private readonly MediaPickerSearchService $picker,
    ) {}

    /** @return array<string, array{media_id: int|null, connected: bool, label: string, fallback: string, selected: array<string, mixed>|null}> */
    public function assets(): array
    {
        $brand = $this->settings->group('brand');

        return [
            'logo' => $this->asset($brand['logo_media_id'], 'Logo', 'TM wordmark'),
            'favicon' => $this->asset($brand['favicon_media_id'], 'Favicon', 'TM initials'),
            'app_icon' => $this->asset($brand['app_icon_media_id'], 'App icon', 'TM initials'),
        ];
    }

    /** @return array{media_id: int|null, connected: bool, label: string, fallback: string, selected: array<string, mixed>|null} */
    private function asset(?int $mediaId, string $label, string $fallback): array
    {
        return [
            'media_id' => $mediaId,
            'connected' => $mediaId !== null,
            'label' => $label,
            'fallback' => $fallback,
            'selected' => $this->picker->option($mediaId ? MediaAsset::query()->find($mediaId) : null),
        ];
    }
}
