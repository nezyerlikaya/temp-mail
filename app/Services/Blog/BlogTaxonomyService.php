<?php

namespace App\Services\Blog;

use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\Locale;
use Illuminate\Support\Collection;

class BlogTaxonomyService
{
    /** @return array<string, string> */
    public function statuses(): array
    {
        return [
            'active' => 'Active',
            'hidden' => 'Hidden',
            'trashed' => 'Trashed',
        ];
    }

    /** @return array<string, int> */
    public function summary(): array
    {
        return [
            'categories' => BlogCategory::query()->count(),
            'tags' => BlogTag::query()->count(),
            'active_categories' => BlogCategory::query()->where('status', 'active')->count(),
            'active_tags' => BlogTag::query()->where('status', 'active')->count(),
        ];
    }

    /** @return Collection<int, Locale> */
    public function locales(): Collection
    {
        return Locale::query()->orderByDesc('is_default')->orderBy('sort_order')->orderBy('language_name')->get();
    }
}
