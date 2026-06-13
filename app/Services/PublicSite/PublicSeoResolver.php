<?php

namespace App\Services\PublicSite;

use App\Models\Locale;
use App\Models\SeoRecord;
use App\Services\Media\MediaUrlResolver;
use App\Services\Seo\SeoRecordResolver;
use App\Services\Seo\SeoSchemaValidator;
use App\Services\Seo\SeoTemplateService;
use Throwable;

class PublicSeoResolver
{
    public function __construct(
        private readonly SeoRecordResolver $records,
        private readonly MediaUrlResolver $media,
        private readonly SeoTemplateService $templates,
        private readonly SeoSchemaValidator $schema,
        private readonly PublicLocaleResolver $locales,
    ) {}

    /** @param array<string, mixed> $brand */
    public function home(Locale $locale, array $brand): array
    {
        $canonical = route('public.home', ['locale' => $locale->locale]);
        $fallbackTitle = $brand['name'];
        $fallbackDescription = $brand['tagline'] ?: 'Private temporary email in seconds.';

        try {
            $record = $this->records->resolve($locale, 'homepage', 'home');

            if (! $record) {
                return [
                    ...$this->fallback($canonical, $fallbackTitle, $fallbackDescription),
                    'hreflang' => $this->homeHreflang(),
                ];
            }

            return [
                'title' => $record->meta_title ?: $fallbackTitle,
                'description' => $record->meta_description ?: $fallbackDescription,
                'canonical' => $record->canonical_url ?: $canonical,
                'robots' => ($record->robots_index ? 'index' : 'noindex').','.($record->robots_follow ? 'follow' : 'nofollow'),
                'og_title' => $record->og_title ?: $record->meta_title ?: $fallbackTitle,
                'og_description' => $record->og_description ?: $record->meta_description ?: $fallbackDescription,
                'og_image' => $record->ogImage ? $this->media->url($record->ogImage) : null,
                'twitter_card' => $record->twitter_card ?: 'summary',
                'twitter_title' => $record->twitter_title ?: $record->og_title ?: $record->meta_title ?: $fallbackTitle,
                'twitter_description' => $record->twitter_description ?: $record->og_description ?: $record->meta_description ?: $fallbackDescription,
                'twitter_image' => $record->twitterImage ? $this->media->url($record->twitterImage) : null,
                'og_type' => 'website',
                'hreflang' => $this->homeHreflang(),
                'schema' => $record->schema_json,
            ];
        } catch (Throwable) {
            return [
                ...$this->fallback($canonical, $fallbackTitle, $fallbackDescription),
                'hreflang' => $this->homeHreflang(),
            ];
        }
    }

    /**
     * @param  array<string, string>  $templateValues
     * @param  array<int, array{locale: string, url: string}>  $hreflang
     */
    public function content(
        Locale $locale,
        string $targetType,
        string $targetKey,
        string $canonical,
        string $fallbackTitle,
        string $fallbackDescription,
        array $templateValues,
        array $hreflang = [],
        ?array $fallbackSchema = null,
        string $ogType = 'website',
    ): array {
        try {
            $record = $this->records->resolve($locale, $targetType, $targetKey);

            if (! $record) {
                return [
                    ...$this->fallback($canonical, $fallbackTitle, $fallbackDescription),
                    'hreflang' => $hreflang,
                    'schema' => $fallbackSchema,
                    'og_type' => $ogType,
                ];
            }

            $defaults = $this->templates->defaultsFor($record, $templateValues);
            $resolvedCanonical = $this->canonical($record, $canonical);

            return [
                'title' => $record->meta_title ?: ($defaults['meta_title'] ?? null) ?: $fallbackTitle,
                'description' => $record->meta_description ?: ($defaults['meta_description'] ?? null) ?: $fallbackDescription,
                'canonical' => $resolvedCanonical,
                'robots' => ($record->robots_index ? 'index' : 'noindex').','.($record->robots_follow ? 'follow' : 'nofollow'),
                'og_type' => $ogType,
                'og_title' => $record->og_title ?: ($defaults['og_title'] ?? null) ?: $record->meta_title ?: $fallbackTitle,
                'og_description' => $record->og_description ?: ($defaults['og_description'] ?? null) ?: $record->meta_description ?: $fallbackDescription,
                'og_image' => $record->ogImage ? $this->media->url($record->ogImage) : null,
                'twitter_card' => $record->twitter_card ?: 'summary_large_image',
                'twitter_title' => $record->twitter_title ?: $record->og_title ?: $fallbackTitle,
                'twitter_description' => $record->twitter_description ?: $record->og_description ?: $fallbackDescription,
                'twitter_image' => $record->twitterImage ? $this->media->url($record->twitterImage) : null,
                'schema' => $this->safeSchema($record, $defaults, $fallbackSchema),
                'hreflang' => collect($hreflang)->reject(fn (array $target): bool => $target['url'] === $resolvedCanonical && $target['locale'] !== $locale->locale)->values()->all(),
            ];
        } catch (Throwable) {
            return [
                ...$this->fallback($canonical, $fallbackTitle, $fallbackDescription),
                'hreflang' => $hreflang,
                'schema' => $fallbackSchema,
                'og_type' => $ogType,
            ];
        }
    }

    private function fallback(string $canonical, string $title, string $description): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => 'index,follow',
            'og_type' => 'website',
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => null,
            'twitter_card' => 'summary',
            'twitter_title' => $title,
            'twitter_description' => $description,
            'twitter_image' => null,
            'hreflang' => [],
            'schema' => null,
        ];
    }

    private function canonical(SeoRecord $record, string $fallback): string
    {
        if (blank($record->canonical_url)) {
            return $fallback;
        }

        $canonical = filter_var($record->canonical_url, FILTER_VALIDATE_URL);

        return is_string($canonical) ? $canonical : $fallback;
    }

    /** @param array<string, mixed> $defaults */
    private function safeSchema(SeoRecord $record, array $defaults, ?array $fallback): ?array
    {
        if (is_array($record->schema_json)) {
            return $record->schema_json;
        }

        $templateSchema = $defaults['schema_json'] ?? null;
        if (is_array($templateSchema)) {
            return $templateSchema;
        }

        if (is_string($templateSchema)) {
            return $this->schema->sanitize($templateSchema);
        }

        return $fallback;
    }

    /** @return array<int, array{locale: string, url: string}> */
    private function homeHreflang(): array
    {
        return $this->locales->available()
            ->map(fn (Locale $locale): array => [
                'locale' => $locale->locale,
                'url' => route('public.home', ['locale' => $locale->locale]),
            ])
            ->values()
            ->all();
    }
}
