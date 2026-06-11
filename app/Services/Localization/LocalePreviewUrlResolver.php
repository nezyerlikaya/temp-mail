<?php

namespace App\Services\Localization;

use App\Models\Locale;

class LocalePreviewUrlResolver
{
    /** @return array{preview: string, translations: string, seo: string} */
    public function urls(Locale $locale): array
    {
        return [
            'preview' => route('admin.locale-launch-center.index', ['preview_locale' => $locale->locale]),
            'translations' => route('admin.translation-center.index', ['locale' => $locale->locale]),
            'seo' => route('admin.seo-growth-center.index', ['locale' => $locale->locale]),
        ];
    }
}
