<?php

namespace App\Services\PublicSite;

use App\Models\Locale;

class PublicNavigationService
{
    public function __construct(private readonly PublicLocaleResolver $locales) {}

    /** @param array<string, string> $translations */
    /** @param array<int, array<string, mixed>>|null $localeSwitcher */
    public function resolve(Locale $locale, array $translations, string $current = 'home', ?array $localeSwitcher = null): array
    {
        return [
            'primary' => [
                [
                    'label' => $translations['nav.home'],
                    'url' => route('public.home', ['locale' => $locale->locale]),
                    'current' => $current === 'home',
                ],
                [
                    'label' => $translations['nav.blog'],
                    'url' => route('public.blog.index', ['locale' => $locale->locale]),
                    'current' => $current === 'blog',
                ],
            ],
            'locale_switcher' => $localeSwitcher ?? $this->locales->available()
                ->map(fn (Locale $available): array => [
                    'code' => $available->locale,
                    'label' => $available->native_name,
                    'url' => route('public.home', ['locale' => $available->locale]),
                    'current' => $available->is($locale),
                    'direction' => $available->direction === 'rtl' ? 'rtl' : 'ltr',
                ])
                ->values()
                ->all(),
        ];
    }
}
