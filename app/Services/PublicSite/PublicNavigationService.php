<?php

namespace App\Services\PublicSite;

use App\Models\Locale;

class PublicNavigationService
{
    public function __construct(private readonly PublicLocaleResolver $locales) {}

    /** @param array<string, string> $translations */
    public function resolve(Locale $locale, array $translations): array
    {
        return [
            'primary' => [
                [
                    'label' => $translations['nav.home'],
                    'url' => route('public.home', ['locale' => $locale->locale]),
                    'current' => true,
                ],
            ],
            'locale_switcher' => $this->locales->available()
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
