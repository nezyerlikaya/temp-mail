<?php

namespace App\Services\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Locale;
use App\Models\User;
use Illuminate\Support\Collection;

class BlogPostStore
{
    /** @return array<string, string> */
    public function statuses(): array
    {
        return [
            'draft' => 'Draft',
            'published' => 'Published',
            'scheduled' => 'Scheduled',
            'hidden' => 'Hidden',
            'trashed' => 'Trashed',
        ];
    }

    /** @return array<string, string> */
    public function contentReadinessOptions(): array
    {
        return [
            'outline' => 'Outline',
            'needs_content' => 'Needs content',
            'needs_review' => 'Needs review',
            'ready' => 'Ready',
        ];
    }

    /** @return array<string, int> */
    public function summary(): array
    {
        return [
            'total' => BlogPost::query()->count(),
            'draft' => BlogPost::query()->where('status', 'draft')->count(),
            'published' => BlogPost::query()->where('status', 'published')->count(),
            'scheduled' => BlogPost::query()->where('status', 'scheduled')->count(),
            'trashed' => BlogPost::query()->where('status', 'trashed')->count(),
        ];
    }

    /** @return Collection<int, Locale> */
    public function locales(): Collection
    {
        return Locale::query()->orderByDesc('is_default')->orderBy('sort_order')->orderBy('language_name')->get();
    }

    /** @return Collection<int, BlogCategory> */
    public function categories(): Collection
    {
        return BlogCategory::query()->with('locale')->orderBy('name')->get();
    }

    /** @return Collection<int, User> */
    public function authors(): Collection
    {
        return User::query()
            ->whereIn('role', ['owner', 'admin', 'editor', 'author'])
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, BlogPost> */
    public function recent(int $limit = 5): Collection
    {
        return BlogPost::query()
            ->with(['locale', 'author'])
            ->where('status', '!=', 'trashed')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
