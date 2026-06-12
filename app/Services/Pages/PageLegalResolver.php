<?php

namespace App\Services\Pages;

use App\Models\Page;
use App\Services\Settings\LegalPageResolver;

class PageLegalResolver
{
    public function __construct(private readonly LegalPageResolver $settingsLegal) {}

    /** @return array<string, string> */
    public function legalTypes(): array
    {
        return [
            'privacy_policy' => 'Privacy Policy',
            'terms_of_service' => 'Terms of Service',
            'cookie_policy' => 'Cookie Policy',
            'abuse' => 'Abuse',
            'dmca' => 'DMCA',
            'contact' => 'Contact',
        ];
    }

    /** @return array{is_legal: bool, label: string|null, mapped: bool, setting_key: string|null} */
    public function readiness(Page $page): array
    {
        $settingKey = $this->settingKeyForType($page->page_type);
        $mapping = $settingKey ? $this->settingsLegal->pages()[$settingKey] ?? null : null;

        return [
            'is_legal' => $settingKey !== null,
            'label' => $this->legalTypes()[$page->page_type] ?? null,
            'mapped' => (int) ($mapping['page_id'] ?? 0) === (int) $page->id,
            'setting_key' => $settingKey,
        ];
    }

    private function settingKeyForType(string $type): ?string
    {
        return [
            'privacy_policy' => 'privacy',
            'terms_of_service' => 'terms',
            'cookie_policy' => 'cookie',
            'abuse' => 'abuse',
            'dmca' => 'dmca',
            'contact' => 'contact',
        ][$type] ?? null;
    }
}
