<?php

namespace App\Services\PublicSite;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Locale;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PublicTaxonomyService
{
    public function __construct(
        private readonly PublicBlogIndexService $blog,
        private readonly PublicViewDataService $base,
        private readonly PublicSeoResolver $seo,
        private readonly PublicHreflangService $hreflang,
        private readonly PublicContentLocaleResolver $localeSwitch,
        private readonly PublicBreadcrumbService $breadcrumbs,
        private readonly PublicAuthorService $authors,
    ) {}

    /** @param array<string, mixed> $theme */
    public function category(Request $request, Locale $locale, array $theme, string $slug): array
    {
        $category = BlogCategory::query()
            ->where('locale_id', $locale->id)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where('status', 'active')
            ->firstOrFail();

        return $this->archive(
            $request,
            $locale,
            $theme,
            'blog_category',
            'blog-category:'.$category->id,
            $category->name,
            $category->description ?: 'Posts filed under '.$category->name.'.',
            route('public.blog.category', ['locale' => $locale->locale, 'slug' => $category->slug]),
            $this->blog->published($locale)->where('blog_category_id', $category->id),
        );
    }

    /** @param array<string, mixed> $theme */
    public function tag(Request $request, Locale $locale, array $theme, string $slug): array
    {
        $tag = BlogTag::query()
            ->where('locale_id', $locale->id)
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        return $this->archive(
            $request,
            $locale,
            $theme,
            'blog_tag',
            'blog-tag:'.$tag->id,
            $tag->name,
            $tag->description ?: 'Posts tagged '.$tag->name.'.',
            route('public.blog.tag', ['locale' => $locale->locale, 'slug' => $tag->slug]),
            $this->blog->published($locale)->whereHas('tags', fn ($query) => $query->whereKey($tag->id)),
        );
    }

    /** @param array<string, mixed> $theme */
    public function author(Request $request, Locale $locale, array $theme, string $slug): array
    {
        $author = User::query()
            ->where('public_author_slug', $slug)
            ->where('author_profile_active', true)
            ->firstOrFail();
        $profile = $this->authors->resolve($author);
        $canonical = route('public.blog.author', ['locale' => $locale->locale, 'slug' => $slug]);
        $title = $profile['name'] ?? $author->name;

        return $this->archive(
            $request,
            $locale,
            $theme,
            'blog_author',
            'blog-author:'.$author->id,
            $title,
            $profile['bio'] ?: 'Articles by '.$title.'.',
            $canonical,
            $this->blog->published($locale)->where('author_id', $author->id),
            $profile,
        );
    }

    /**
     * @param  array<string, mixed>  $theme
     * @param  Builder<BlogPost>  $query
     * @param  array<string, mixed>|null  $author
     */
    private function archive(Request $request, Locale $locale, array $theme, string $targetType, string $targetKey, string $title, string $description, string $canonical, $query, ?array $author = null): array
    {
        $seo = $this->seo->content(
            $locale,
            $targetType,
            $targetKey,
            $canonical,
            $title,
            $description,
            ['category_name' => $title, 'tag_name' => $title, 'locale_name' => $locale->language_name, 'site_name' => config('app.name')],
            $this->hreflang->exact($locale, $canonical),
            ['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => $title, 'url' => $canonical],
        );

        return [
            ...$this->base->content($locale, $theme, $seo, 'blog', $this->localeSwitch->switches($locale, 'blog')),
            'page_heading' => $title,
            'page_description' => $description,
            'archive_author' => $author,
            'posts' => $this->blog->paginate($query, $request),
            'breadcrumbs' => $this->breadcrumbs->blog($locale, $title),
        ];
    }
}
