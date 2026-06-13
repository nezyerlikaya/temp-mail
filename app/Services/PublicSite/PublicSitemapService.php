<?php

namespace App\Services\PublicSite;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Locale;
use App\Models\Page;
use App\Models\SeoRecord;

class PublicSitemapService
{
    public function __construct(private readonly PublicLocaleResolver $locales) {}

    public function xml(): string
    {
        $urls = $this->locales->available()->flatMap(fn (Locale $locale) => $this->urlsFor($locale));
        $body = $urls->map(function (array $item): string {
            $lastmod = $item['lastmod'] ? '<lastmod>'.e($item['lastmod']).'</lastmod>' : '';
            $changefreq = '<changefreq>'.e($item['changefreq']).'</changefreq>';
            $priority = '<priority>'.e($item['priority']).'</priority>';

            return '<url><loc>'.e($item['url']).'</loc>'.$lastmod.$changefreq.$priority.'</url>';
        })->implode('');

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$body.'</urlset>';
    }

    private function urlsFor(Locale $locale)
    {
        $records = SeoRecord::query()->where('locale_id', $locale->id)->get()->keyBy(fn (SeoRecord $record): string => $record->target_type.'|'.$record->target_key);
        $urls = collect();

        if ($this->included($records->get('homepage|home'))) {
            $urls->push($this->entry(route('public.home', ['locale' => $locale->locale]), null, $records->get('homepage|home'), 'daily', '1.0'));
        }

        $blogIndex = $records->get('blog_index|blog');
        if ($this->included($blogIndex)) {
            $urls->push($this->entry(route('public.blog.index', ['locale' => $locale->locale]), null, $blogIndex, 'weekly', '0.7'));
        }

        Page::query()->where('locale_id', $locale->id)->where('status', 'published')->whereNull('trashed_at')->whereNotNull('published_at')->where('published_at', '<=', now())
            ->get()->each(function (Page $page) use ($locale, $records, $urls): void {
                $record = $records->get('page|page:'.$page->id);
                if ($this->included($record)) {
                    $urls->push($this->entry(route('public.pages.show', ['locale' => $locale->locale, 'slug' => $page->slug]), $page->updated_at?->toDateString(), $record));
                }
            });

        BlogPost::query()->where('locale_id', $locale->id)->where('status', 'published')->whereNull('trashed_at')->whereNotNull('published_at')->where('published_at', '<=', now())
            ->get()->each(function (BlogPost $post) use ($locale, $records, $urls): void {
                $record = $records->get('blog_post|blog-post:'.$post->id);
                if ($this->included($record)) {
                    $urls->push($this->entry(route('public.blog.show', ['locale' => $locale->locale, 'slug' => $post->slug]), $post->updated_at?->toDateString(), $record, 'weekly', '0.7'));
                }
            });

        BlogCategory::query()->where('locale_id', $locale->id)->where('status', 'active')->where('is_active', true)->get()
            ->each(function (BlogCategory $category) use ($locale, $records, $urls): void {
                $record = $records->get('blog_category|blog-category:'.$category->id);
                if ($this->included($record)) {
                    $urls->push($this->entry(route('public.blog.category', ['locale' => $locale->locale, 'slug' => $category->slug]), $category->updated_at?->toDateString(), $record));
                }
            });

        BlogTag::query()->where('locale_id', $locale->id)->where('status', 'active')->get()
            ->each(function (BlogTag $tag) use ($locale, $records, $urls): void {
                $record = $records->get('blog_tag|blog-tag:'.$tag->id);
                if ($this->included($record)) {
                    $urls->push($this->entry(route('public.blog.tag', ['locale' => $locale->locale, 'slug' => $tag->slug]), $tag->updated_at?->toDateString(), $record));
                }
            });

        return $urls;
    }

    private function included(?SeoRecord $record): bool
    {
        return ! $record || ($record->robots_index && $record->include_in_sitemap);
    }

    /** @return array<string, string|null> */
    private function entry(string $url, ?string $lastmod, ?SeoRecord $record, string $frequency = 'monthly', string $priority = '0.5'): array
    {
        return [
            'url' => $url,
            'lastmod' => $lastmod,
            'changefreq' => $record?->sitemap_change_frequency ?: $frequency,
            'priority' => $record?->sitemap_priority ?: $priority,
        ];
    }
}
