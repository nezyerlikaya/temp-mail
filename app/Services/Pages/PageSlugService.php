<?php

namespace App\Services\Pages;

use App\Models\Page;

class PageSlugService
{
    public function normalize(string $value): string
    {
        return str($value)->lower()->slug('-')->limit(120, '')->toString();
    }

    public function fromTitle(string $title): string
    {
        return $this->normalize($title) ?: 'page';
    }

    public function uniqueForLocale(string $slug, int $localeId, ?int $ignorePageId = null): bool
    {
        return ! Page::query()
            ->where('locale_id', $localeId)
            ->where('slug', $slug)
            ->when($ignorePageId, fn ($query): mixed => $query->whereKeyNot($ignorePageId))
            ->exists();
    }
}
