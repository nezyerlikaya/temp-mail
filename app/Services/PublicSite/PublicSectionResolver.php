<?php

namespace App\Services\PublicSite;

use App\Models\BlogPost;
use App\Models\Locale;
use App\Services\Sections\SectionCollectionResolver;
use App\Services\Sections\SectionRenderService;
use Illuminate\Support\Facades\Schema;

class PublicSectionResolver
{
    public function __construct(
        private readonly SectionCollectionResolver $collections,
        private readonly SectionRenderService $render,
    ) {}

    /** @return array<string, array<int, array<string, mixed>>> */
    public function home(Locale $locale): array
    {
        $sections = collect([
            ...$this->collections->resolve($locale, 'home.primary')->all(),
            ...$this->collections->resolve($locale, 'home.secondary')->all(),
            ...$this->collections->resolve($locale, 'home.faq')->all(),
        ])
            ->map(fn ($section): ?array => $this->render->renderable($section))
            ->filter()
            ->map(fn (array $section): array => $section['type'] === 'cta' ? $this->ctaUrl($section, $locale) : $section)
            ->map(fn (array $section): array => $section['type'] === 'blog_teaser'
                ? [...$section, 'posts' => $this->blogPosts($locale, $section)]
                : $section)
            ->filter(fn (array $section): bool => $section['type'] !== 'blog_teaser' || $section['posts'] !== [])
            ->values();

        return [
            'cta' => $sections->where('type', 'cta')->values()->all(),
            'faq' => $sections->where('type', 'faq')->values()->all(),
            'blog_teaser' => $sections->where('type', 'blog_teaser')->values()->all(),
            'feature_grid' => $sections->where('type', 'feature_grid')->values()->all(),
            'trust_security' => $sections->where('type', 'trust_security')->values()->all(),
            'abuse_notice' => $sections->where('type', 'abuse_notice')->values()->all(),
            'cookie_notice' => $sections->where('type', 'cookie_notice')->values()->all(),
        ];
    }

    /** @param array<string, mixed> $section @return array<int, array<string, string|null>> */
    private function blogPosts(Locale $locale, array $section): array
    {
        if (! Schema::hasTable('blog_posts')) {
            return [];
        }

        $query = BlogPost::query()
            ->where('locale_id', $locale->id)
            ->where('status', 'published')
            ->whereNull('trashed_at')
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        if (filled($section['category_id'] ?? null)) {
            $query->where('blog_category_id', (int) $section['category_id']);
        }

        return $query->limit(max(1, min(6, (int) ($section['post_count'] ?? 3))))
            ->get()
            ->map(fn (BlogPost $post): array => [
                'title' => $post->title,
                'excerpt' => $post->excerpt,
                'published_at' => $post->published_at?->toFormattedDateString(),
            ])
            ->all();
    }

    /** @param array<string, mixed> $section @return array<string, mixed> */
    private function ctaUrl(array $section, Locale $locale): array
    {
        $url = (string) ($section['button_url'] ?? '');

        return [
            ...$section,
            'button_url' => str_starts_with($url, '#')
                ? $url
                : route('public.home', ['locale' => $locale->locale]),
        ];
    }
}
