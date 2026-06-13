<?php

namespace App\Services\Seo;

use App\Models\Locale;
use App\Models\SeoRecord;

class SeoRecordResolver
{
    public function __construct(private readonly SeoTargetRegistry $targets) {}

    public function resolve(Locale|int $locale, string $targetType, string $targetKey): ?SeoRecord
    {
        $localeId = $locale instanceof Locale ? $locale->id : $locale;

        return SeoRecord::query()
            ->with(['locale', 'ogImage'])
            ->where('locale_id', $localeId)
            ->where('target_type', $targetType)
            ->where('target_key', $targetKey)
            ->first();
    }

    /** @return array<string, mixed> */
    public function defaultsForTarget(array $target): array
    {
        return [
            'locale_id' => $target['locale_id'],
            'target_type' => $target['target_type'],
            'target_key' => $target['target_key'],
            'targetable_type' => $target['targetable_type'],
            'targetable_id' => $target['targetable_id'],
            'canonical_url' => $this->canonicalUrl($target),
            'robots_index' => true,
            'robots_follow' => true,
            'include_in_sitemap' => true,
            'sitemap_priority' => $this->priorityFor((string) $target['target_type']),
            'sitemap_change_frequency' => $this->changeFrequencyFor((string) $target['target_type']),
            'twitter_card' => 'summary_large_image',
            'schema_type' => $this->schemaTypeFor((string) $target['target_type']),
        ];
    }

    /** @return array<string, mixed>|null */
    public function targetFor(SeoRecord $record): ?array
    {
        return $this->targets->find($record->locale_id, $record->target_type, $record->target_key);
    }

    /** @param array<string, mixed> $target */
    public function canonicalUrl(array $target): string
    {
        return url($target['canonical_path']);
    }

    private function priorityFor(string $targetType): float
    {
        return match ($targetType) {
            'homepage', 'temporary_email_generator' => 1.0,
            'pricing', 'disposable_email', 'ten_minute_mail' => 0.8,
            'blog_post', 'blog_index', 'page', 'language_landing' => 0.7,
            default => 0.5,
        };
    }

    private function changeFrequencyFor(string $targetType): string
    {
        return match ($targetType) {
            'homepage', 'temporary_email_generator', 'inbox' => 'daily',
            'blog_post', 'blog_index', 'blog_category', 'blog_tag', 'blog_author' => 'weekly',
            default => 'monthly',
        };
    }

    private function schemaTypeFor(string $targetType): ?string
    {
        return match ($targetType) {
            'homepage', 'language_landing' => 'WebSite',
            'blog_post' => 'Article',
            'blog_index' => 'Blog',
            'blog_category', 'blog_tag', 'blog_author' => 'CollectionPage',
            'page' => 'WebPage',
            default => null,
        };
    }
}
