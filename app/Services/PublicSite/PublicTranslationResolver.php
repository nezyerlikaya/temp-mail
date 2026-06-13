<?php

namespace App\Services\PublicSite;

use App\Models\Locale;
use App\Models\TranslationSource;
use App\Services\Translations\TranslationFallbackResolver;
use App\Services\Translations\TranslationSourceRegistry;
use Illuminate\Support\Facades\Schema;

class PublicTranslationResolver
{
    public function __construct(
        private readonly TranslationFallbackResolver $fallback,
        private readonly TranslationSourceRegistry $registry,
    ) {}

    /** @return array<string, string> */
    public function resolve(Locale $locale, array $keys): array
    {
        $canonical = collect($this->registry->sources())
            ->mapWithKeys(fn (array $source): array => [$source['translation_key'] => $source['source_value']]);

        if (! Schema::hasTable('translation_sources')) {
            return collect($keys)
                ->mapWithKeys(fn (string $key): array => [$key => (string) $canonical->get($key, $key)])
                ->all();
        }

        $sources = TranslationSource::query()
            ->where('is_active', true)
            ->whereIn('translation_key', $keys)
            ->get()
            ->keyBy('translation_key');

        return collect($keys)->mapWithKeys(function (string $key) use ($canonical, $locale, $sources): array {
            $source = $sources->get($key);

            return [
                $key => $source
                    ? $this->fallback->resolve($source, $locale)
                    : (string) $canonical->get($key, $key),
            ];
        })->all();
    }
}
