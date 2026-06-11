<?php

namespace App\Services\Settings;

class LegalPageResolver
{
    public function __construct(private readonly SettingsResolver $settings) {}

    /** @return array<string, array{page_id: int|null, connected: bool, label: string}> */
    public function pages(): array
    {
        $legal = $this->settings->group('legal');
        $labels = [
            'privacy' => 'Privacy page', 'terms' => 'Terms page', 'cookie' => 'Cookie page',
            'abuse' => 'Abuse page', 'dmca' => 'DMCA page', 'contact' => 'Contact page',
        ];

        return collect($labels)->mapWithKeys(function (string $label, string $key) use ($legal): array {
            $pageId = $legal[$key.'_page_id'];

            return [$key => ['page_id' => $pageId, 'connected' => $pageId !== null, 'label' => $label]];
        })->all();
    }
}
