<?php

namespace App\Services\Translations;

use App\Models\Locale;
use App\Models\TranslationSource;
use App\Models\TranslationValue;

class TranslationFallbackResolver
{
    public function resolve(TranslationSource $source, Locale|string $locale): string
    {
        $localeId = $locale instanceof Locale
            ? $locale->id
            : Locale::query()->where('locale', $locale)->value('id');

        if ($localeId === null) {
            return $source->source_value;
        }

        $translated = TranslationValue::query()
            ->where('translation_source_id', $source->id)
            ->where('locale_id', $localeId)
            ->where('status', 'published')
            ->value('value');

        return filled($translated) ? (string) $translated : $source->source_value;
    }
}
