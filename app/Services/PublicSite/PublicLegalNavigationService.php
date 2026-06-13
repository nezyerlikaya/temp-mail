<?php

namespace App\Services\PublicSite;

use App\Models\Page;
use App\Services\Settings\SettingsResolver;

class PublicLegalNavigationService
{
    public function __construct(private readonly SettingsResolver $settings) {}

    /** @return array<int, array{label: string, url: string}> */
    public function links(int $localeId, string $locale): array
    {
        $legal = $this->settings->group('legal');
        $labels = [
            'privacy_page_id' => 'Privacy Policy',
            'terms_page_id' => 'Terms of Service',
            'cookie_page_id' => 'Cookie Policy',
            'abuse_page_id' => 'Abuse',
            'dmca_page_id' => 'DMCA',
            'contact_page_id' => 'Contact',
        ];

        $ids = collect(array_keys($labels))->map(fn (string $key): int => (int) ($legal[$key] ?? 0))->filter()->all();

        return Page::query()
            ->whereIn('id', $ids)
            ->where('locale_id', $localeId)
            ->where('status', 'published')
            ->whereNull('trashed_at')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get()
            ->map(function (Page $page) use ($labels, $legal, $locale): array {
                $key = collect($legal)->search(fn (mixed $value, string $key): bool => str_ends_with($key, '_page_id') && (int) $value === (int) $page->id);

                return [
                    'label' => is_string($key) ? ($labels[$key] ?? $page->title) : $page->title,
                    'url' => route('public.pages.show', ['locale' => $locale, 'slug' => $page->slug]),
                ];
            })
            ->values()
            ->all();
    }
}
