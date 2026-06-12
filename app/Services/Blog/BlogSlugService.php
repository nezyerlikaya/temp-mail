<?php

namespace App\Services\Blog;

use App\Models\BlogPost;

class BlogSlugService
{
    public function normalize(string $value): string
    {
        return str($value)->lower()->slug('-')->limit(120, '')->toString();
    }

    public function fromTitle(string $title): string
    {
        return $this->normalize($title) ?: 'post';
    }

    public function uniqueForLocale(string $slug, int $localeId, ?int $ignorePostId = null): bool
    {
        return ! BlogPost::query()
            ->where('locale_id', $localeId)
            ->where('slug', $slug)
            ->when($ignorePostId, fn ($query): mixed => $query->whereKeyNot($ignorePostId))
            ->exists();
    }
}
