<?php

namespace App\Services\PublicSite;

use App\Models\Locale;

class PublicHreflangService
{
    public function __construct(private readonly PublicLocaleResolver $locales) {}

    /** @return array<int, array{locale: string, url: string}> */
    public function indexes(string $route): array
    {
        return $this->locales->available()
            ->map(fn (Locale $locale): array => [
                'locale' => $locale->locale,
                'url' => route($route, ['locale' => $locale->locale]),
            ])
            ->values()
            ->all();
    }

    /** @return array<int, array{locale: string, url: string}> */
    public function exact(Locale $locale, string $url): array
    {
        return [['locale' => $locale->locale, 'url' => $url]];
    }
}
