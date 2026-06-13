<?php

namespace App\Services\PublicSite;

use App\Models\Locale;

class PublicContentLocaleResolver
{
    public function __construct(private readonly PublicLocaleResolver $locales) {}

    /** @return array<int, array<string, mixed>> */
    public function switches(Locale $current, string $context): array
    {
        return $this->locales->available()
            ->map(fn (Locale $locale): array => [
                'code' => $locale->locale,
                'label' => $locale->native_name,
                'url' => $context === 'blog'
                    ? route('public.blog.index', ['locale' => $locale->locale])
                    : route('public.home', ['locale' => $locale->locale]),
                'current' => $locale->is($current),
                'direction' => $locale->direction === 'rtl' ? 'rtl' : 'ltr',
            ])
            ->values()
            ->all();
    }
}
