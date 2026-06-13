<?php

namespace App\Services\PublicSite;

use App\Models\BlogPost;
use App\Models\Comment;
use App\Models\Locale;
use App\Services\Comments\CommentSettingsStore;
use Illuminate\Http\Request;

class PublicBlogPostService
{
    public function __construct(
        private readonly PublicViewDataService $base,
        private readonly PublicSeoResolver $seo,
        private readonly PublicHreflangService $hreflang,
        private readonly PublicContentLocaleResolver $localeSwitch,
        private readonly PublicContentFormatter $content,
        private readonly PublicAuthorService $authors,
        private readonly PublicBreadcrumbService $breadcrumbs,
        private readonly PublicBlogIndexService $blog,
        private readonly CommentSettingsStore $commentSettings,
    ) {}

    /** @param array<string, mixed> $theme */
    public function show(Request $request, Locale $locale, array $theme, string $slug): array
    {
        $post = $this->blog->published($locale)
            ->where('slug', $slug)
            ->firstOrFail();
        $canonical = route('public.blog.show', ['locale' => $locale->locale, 'slug' => $post->slug]);
        $description = $post->excerpt ?: str($post->content)->stripTags()->limit(155)->toString();
        $image = $this->content->image($post->featuredMedia);
        $author = $this->authors->resolve($post->author);
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'mainEntityOfPage' => $canonical,
            'author' => ['@type' => 'Person', 'name' => $author['name'] ?? config('app.name')],
        ];
        if ($image) {
            $schema['image'] = $image['url'];
        }

        $seo = $this->seo->content(
            $locale,
            'blog_post',
            'blog-post:'.$post->id,
            $canonical,
            $post->title,
            $description,
            ['post_title' => $post->title, 'locale_name' => $locale->language_name, 'site_name' => config('app.name')],
            $this->hreflang->exact($locale, $canonical),
            $schema,
            'article',
        );
        $commentsOpen = $this->commentSettings->acceptsComments($post);

        return [
            ...$this->base->content($locale, $theme, $seo, 'blog', $this->localeSwitch->switches($locale, 'blog')),
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'excerpt' => $post->excerpt,
                'content_html' => $this->content->html($post->content),
                'published_at' => $post->published_at?->toFormattedDateString(),
                'image' => $image,
                'author' => $author,
                'category' => $post->category && $post->category->status === 'active' && $post->category->is_active ? [
                    'name' => $post->category->name,
                    'url' => route('public.blog.category', ['locale' => $locale->locale, 'slug' => $post->category->slug]),
                ] : null,
                'tags' => $post->tags->where('status', 'active')->map(fn ($tag): array => [
                    'name' => $tag->name,
                    'url' => route('public.blog.tag', ['locale' => $locale->locale, 'slug' => $tag->slug]),
                ])->values()->all(),
            ],
            'related_posts' => $this->related($post, $locale),
            'comments' => $this->comments($post),
            'comments_open' => $commentsOpen,
            'comment_action' => $commentsOpen ? route('public.blog.comments.store', ['locale' => $locale->locale, 'post' => $post]) : null,
            'breadcrumbs' => $this->breadcrumbs->blog($locale, $post->title),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function related(BlogPost $post, Locale $locale): array
    {
        return $this->blog->published($locale)
            ->whereKeyNot($post->id)
            ->when($post->blog_category_id, fn ($query) => $query->where('blog_category_id', $post->blog_category_id))
            ->limit(3)
            ->get()
            ->map(fn (BlogPost $related): array => $this->blog->card($related, $locale->locale))
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function comments(BlogPost $post): array
    {
        return Comment::query()
            ->with(['replies' => fn ($query) => $query->where('status', 'approved')->whereNull('trashed_at')->orderBy('created_at')])
            ->where('blog_post_id', $post->id)
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->whereNull('trashed_at')
            ->orderBy('created_at')
            ->get()
            ->map(fn (Comment $comment): array => [
                'author' => $comment->author_name,
                'content' => $comment->content,
                'created_at' => $comment->created_at?->diffForHumans(),
                'replies' => $comment->replies->map(fn (Comment $reply): array => [
                    'author' => $reply->author_name,
                    'content' => $reply->content,
                    'created_at' => $reply->created_at?->diffForHumans(),
                ])->all(),
            ])
            ->all();
    }
}
