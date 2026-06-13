<?php

namespace App\Services\PublicSite;

use App\Models\Locale;
use App\Models\Page;
use App\Services\Settings\SettingsResolver;
use Illuminate\Http\Request;

class PublicPageService
{
    public function __construct(
        private readonly PublicViewDataService $base,
        private readonly PublicSeoResolver $seo,
        private readonly PublicHreflangService $hreflang,
        private readonly PublicContentLocaleResolver $localeSwitch,
        private readonly PublicContentFormatter $content,
        private readonly PublicAuthorService $authors,
        private readonly PublicBreadcrumbService $breadcrumbs,
        private readonly SettingsResolver $settings,
    ) {}

    /** @param array<string, mixed> $theme */
    public function show(Request $request, Locale $locale, array $theme, string $slug): array
    {
        $page = Page::query()
            ->with(['featuredMedia', 'author'])
            ->where('locale_id', $locale->id)
            ->where('slug', $slug)
            ->where('status', 'published')
            ->whereNull('trashed_at')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        $this->ensureLegalMapping($page);

        $canonical = route('public.pages.show', ['locale' => $locale->locale, 'slug' => $page->slug]);
        $description = $page->excerpt ?: str($page->content)->stripTags()->limit(155)->toString();
        $seo = $this->seo->content(
            $locale,
            'page',
            'page:'.$page->id,
            $canonical,
            $page->title,
            $description,
            ['page_title' => $page->title, 'locale_name' => $locale->language_name, 'site_name' => config('app.name')],
            $this->hreflang->exact($locale, $canonical),
            ['@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => $page->title, 'url' => $canonical],
        );

        return [
            ...$this->base->content($locale, $theme, $seo, 'home', $this->localeSwitch->switches($locale, 'home')),
            'page' => [
                'title' => $page->title,
                'excerpt' => $page->excerpt,
                'content_html' => $this->content->html($page->content),
                'image' => $this->content->image($page->featuredMedia),
                'author' => $this->authors->resolve($page->author),
                'published_at' => $page->published_at?->toFormattedDateString(),
                'updated_at' => $page->updated_at?->toFormattedDateString(),
                'type' => $page->page_type,
            ],
            'breadcrumbs' => $this->breadcrumbs->page($locale, $page->title),
        ];
    }

    private function ensureLegalMapping(Page $page): void
    {
        $key = [
            'privacy_policy' => 'privacy_page_id',
            'terms_of_service' => 'terms_page_id',
            'cookie_policy' => 'cookie_page_id',
            'abuse' => 'abuse_page_id',
            'dmca' => 'dmca_page_id',
            'contact' => 'contact_page_id',
        ][$page->page_type] ?? null;

        if ($key) {
            abort_unless((int) ($this->settings->group('legal')[$key] ?? 0) === (int) $page->id, 404);
        }
    }
}
