<?php

namespace App\Services\PublicSite;

use App\Models\Locale;
use App\Services\Media\MediaUrlResolver;
use App\Services\Seo\SeoRecordResolver;
use Throwable;

class PublicSeoResolver
{
    public function __construct(
        private readonly SeoRecordResolver $records,
        private readonly MediaUrlResolver $media,
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
                return $this->fallback($canonical, $fallbackTitle, $fallbackDescription);
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
                'schema' => $record->schema_json,
            ];
        } catch (Throwable) {
            return $this->fallback($canonical, $fallbackTitle, $fallbackDescription);
        }
    }

    private function fallback(string $canonical, string $title, string $description): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => 'index,follow',
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => null,
            'twitter_card' => 'summary',
            'schema' => null,
        ];
    }
}
