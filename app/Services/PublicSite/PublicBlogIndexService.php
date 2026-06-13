<?php

namespace App\Services\PublicSite;

use App\Models\BlogPost;
use App\Models\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PublicBlogIndexService
{
    public function __construct(
        private readonly PublicViewDataService $base,
        private readonly PublicSeoResolver $seo,
        private readonly PublicHreflangService $hreflang,
        private readonly PublicContentLocaleResolver $localeSwitch,
        private readonly PublicContentFormatter $content,
        private readonly PublicAuthorService $authors,
        private readonly PublicBreadcrumbService $breadcrumbs,
    ) {}

    /** @param array<string, mixed> $theme */
    public function index(Request $request, Locale $locale, array $theme): array
    {
        $canonical = route('public.blog.index', ['locale' => $locale->locale]);
        $seo = $this->seo->content(
            $locale,
            'blog_index',
            'blog',
            $canonical,
            'Blog',
            'Privacy, security, and temporary email guidance.',
            ['locale_name' => $locale->language_name, 'site_name' => config('app.name')],
            $this->hreflang->indexes('public.blog.index'),
            ['@context' => 'https://schema.org', '@type' => 'Blog', 'url' => $canonical, 'name' => 'Blog'],
        );

        return [
            ...$this->base->content($locale, $theme, $seo, 'blog', $this->localeSwitch->switches($locale, 'blog')),
            'page_heading' => 'Blog',
            'page_description' => 'Privacy, security, and temporary email guidance.',
            'posts' => $this->paginate($this->published($locale), $request),
            'breadcrumbs' => $this->breadcrumbs->blog($locale),
        ];
    }

    /** @return Builder<BlogPost> */
    public function published(Locale $locale): Builder
    {
        return BlogPost::query()
            ->with(['featuredMedia', 'category', 'tags', 'author'])
            ->where('locale_id', $locale->id)
            ->where('status', 'published')
            ->whereNull('trashed_at')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at');
    }

    /** @param Builder<BlogPost> $query */
    public function paginate(Builder $query, Request $request): array
    {
        $paginator = $query->paginate(9)->withQueryString();

        $locale = (string) $request->route('locale');

        return [
            'items' => $paginator->getCollection()->map(fn (BlogPost $post): array => $this->card($post, $locale))->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'previous_url' => $paginator->previousPageUrl(),
                'next_url' => $paginator->nextPageUrl(),
                'links' => collect($paginator->linkCollection())->map(fn (array $link): array => [
                    'url' => $link['url'],
                    'label' => strip_tags($link['label']),
                    'active' => $link['active'],
                ])->all(),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function card(BlogPost $post, string $locale): array
    {
        return [
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'url' => route('public.blog.show', ['locale' => $locale, 'slug' => $post->slug]),
            'published_at' => $post->published_at?->toFormattedDateString(),
            'image' => $this->content->image($post->featuredMedia),
            'category' => $post->category && $post->category->status === 'active' && $post->category->is_active ? [
                'name' => $post->category->name,
                'url' => route('public.blog.category', ['locale' => $locale, 'slug' => $post->category->slug]),
            ] : null,
            'author' => $this->authors->resolve($post->author),
        ];
    }
}
