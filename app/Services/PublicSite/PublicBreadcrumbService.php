<?php

namespace App\Services\PublicSite;

use App\Models\Locale;

class PublicBreadcrumbService
{
    /** @return array<int, array{label: string, url: string|null}> */
    public function blog(Locale $locale, ?string $label = null, ?string $url = null): array
    {
        $items = [
            ['label' => 'Home', 'url' => route('public.home', ['locale' => $locale->locale])],
            ['label' => 'Blog', 'url' => route('public.blog.index', ['locale' => $locale->locale])],
        ];

        if ($label) {
            $items[] = ['label' => $label, 'url' => $url];
        }

        return $items;
    }

    /** @return array<int, array{label: string, url: string|null}> */
    public function page(Locale $locale, string $label): array
    {
        return [
            ['label' => 'Home', 'url' => route('public.home', ['locale' => $locale->locale])],
            ['label' => $label, 'url' => null],
        ];
    }
}
