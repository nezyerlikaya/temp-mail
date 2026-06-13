<?php

namespace App\Services\Seo;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Locale;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class SeoTargetRegistry
{
    /** @return array<string, string> */
    public function targetTypes(): array
    {
        return [
            'homepage' => 'Homepage',
            'temporary_email_generator' => 'Temporary email generator',
            'disposable_email' => 'Disposable email',
            'ten_minute_mail' => '10 minute mail',
            'inbox' => 'Inbox',
            'pricing' => 'Pricing',
            'blog_post' => 'Blog posts',
            'blog_index' => 'Blog indexes',
            'blog_category' => 'Blog categories',
            'blog_tag' => 'Blog tags',
            'blog_author' => 'Blog authors',
            'page' => 'Pages',
            'language_landing' => 'Language landing pages',
        ];
    }

    /** @return array<int, string> */
    public function keys(): array
    {
        return array_keys($this->targetTypes());
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function targets(?Locale $locale = null): Collection
    {
        $locales = $locale
            ? collect([$locale])
            : Locale::query()->orderByDesc('is_default')->orderBy('sort_order')->orderBy('language_name')->get();

        return $locales->flatMap(fn (Locale $market): Collection => collect([
            $this->staticTarget($market, 'homepage', 'home', 'Homepage', '/'.$market->locale),
            $this->staticTarget($market, 'temporary_email_generator', 'generator', 'Temporary email generator', '/'.$market->locale.'/temp-mail'),
            $this->staticTarget($market, 'disposable_email', 'disposable-email', 'Disposable email', '/'.$market->locale.'/disposable-email'),
            $this->staticTarget($market, 'ten_minute_mail', '10-minute-mail', '10 minute mail', '/'.$market->locale.'/10-minute-mail'),
            $this->staticTarget($market, 'inbox', 'inbox', 'Inbox', '/'.$market->locale.'/inbox'),
            $this->staticTarget($market, 'pricing', 'pricing', 'Pricing', '/'.$market->locale.'/pricing'),
            $this->staticTarget($market, 'language_landing', $market->locale, $market->language_name.' landing page', '/'.$market->locale),
            $this->staticTarget($market, 'blog_index', 'blog', $market->language_name.' blog index', '/'.$market->locale.'/blog'),
            ...$this->pageTargets($market)->all(),
            ...$this->blogPostTargets($market)->all(),
            ...$this->blogCategoryTargets($market)->all(),
            ...$this->blogTagTargets($market)->all(),
            ...$this->blogAuthorTargets($market)->all(),
        ]))->values();
    }

    /** @return array<string, mixed>|null */
    public function find(int $localeId, string $targetType, string $targetKey): ?array
    {
        $locale = Locale::query()->find($localeId);

        if (! $locale) {
            return null;
        }

        return $this->targets($locale)
            ->first(fn (array $target): bool => $target['target_type'] === $targetType && $target['target_key'] === $targetKey);
    }

    /** @return array<string, mixed> */
    private function staticTarget(Locale $locale, string $type, string $key, string $label, string $path): array
    {
        return [
            'locale_id' => $locale->id,
            'locale' => $locale,
            'target_type' => $type,
            'target_key' => $key,
            'label' => $label,
            'description' => 'System route SEO readiness for '.$locale->language_name.'.',
            'canonical_path' => $path,
            'targetable_type' => null,
            'targetable_id' => null,
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    private function pageTargets(Locale $locale): Collection
    {
        if (! Schema::hasTable('pages')) {
            return collect();
        }

        return Page::query()
            ->where('locale_id', $locale->id)
            ->orderBy('title')
            ->get()
            ->map(fn (Page $page): array => [
                'locale_id' => $locale->id,
                'locale' => $locale,
                'target_type' => 'page',
                'target_key' => 'page:'.$page->id,
                'label' => $page->title,
                'description' => 'Page Studio content target. SEO metadata stays separate.',
                'canonical_path' => '/'.$locale->locale.'/'.$page->slug,
                'targetable_type' => Page::class,
                'targetable_id' => $page->id,
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function blogPostTargets(Locale $locale): Collection
    {
        if (! Schema::hasTable('blog_posts')) {
            return collect();
        }

        return BlogPost::query()
            ->where('locale_id', $locale->id)
            ->orderByDesc('published_at')
            ->orderBy('title')
            ->get()
            ->map(fn (BlogPost $post): array => [
                'locale_id' => $locale->id,
                'locale' => $locale,
                'target_type' => 'blog_post',
                'target_key' => 'blog-post:'.$post->id,
                'label' => $post->title,
                'description' => 'Blog Studio post target. SEO does not own post content.',
                'canonical_path' => '/'.$locale->locale.'/blog/'.$post->slug,
                'targetable_type' => BlogPost::class,
                'targetable_id' => $post->id,
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function blogCategoryTargets(Locale $locale): Collection
    {
        if (! Schema::hasTable('blog_categories')) {
            return collect();
        }

        return BlogCategory::query()
            ->where('locale_id', $locale->id)
            ->orderBy('name')
            ->get()
            ->map(fn (BlogCategory $category): array => [
                'locale_id' => $locale->id,
                'locale' => $locale,
                'target_type' => 'blog_category',
                'target_key' => 'blog-category:'.$category->id,
                'label' => $category->name,
                'description' => 'Blog category archive target.',
                'canonical_path' => '/'.$locale->locale.'/blog/category/'.$category->slug,
                'targetable_type' => BlogCategory::class,
                'targetable_id' => $category->id,
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function blogTagTargets(Locale $locale): Collection
    {
        if (! Schema::hasTable('blog_tags')) {
            return collect();
        }

        return BlogTag::query()
            ->where('locale_id', $locale->id)
            ->orderBy('name')
            ->get()
            ->map(fn (BlogTag $tag): array => [
                'locale_id' => $locale->id,
                'locale' => $locale,
                'target_type' => 'blog_tag',
                'target_key' => 'blog-tag:'.$tag->id,
                'label' => $tag->name,
                'description' => 'Blog tag archive target.',
                'canonical_path' => '/'.$locale->locale.'/blog/tag/'.$tag->slug,
                'targetable_type' => BlogTag::class,
                'targetable_id' => $tag->id,
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function blogAuthorTargets(Locale $locale): Collection
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('blog_posts')) {
            return collect();
        }

        return User::query()
            ->where('author_profile_active', true)
            ->whereNotNull('public_author_slug')
            ->whereHas('posts', fn ($query) => $query->where('locale_id', $locale->id))
            ->orderBy('display_name')
            ->orderBy('name')
            ->get()
            ->map(fn (User $author): array => [
                'locale_id' => $locale->id,
                'locale' => $locale,
                'target_type' => 'blog_author',
                'target_key' => 'blog-author:'.$author->id,
                'label' => $author->display_name ?: $author->name,
                'description' => 'Public author archive target.',
                'canonical_path' => '/'.$locale->locale.'/blog/author/'.$author->public_author_slug,
                'targetable_type' => User::class,
                'targetable_id' => $author->id,
            ]);
    }
}
