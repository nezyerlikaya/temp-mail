<?php

namespace App\Services\PublicSite;

use App\Models\Locale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class PublicLocaleResolver
{
    public function find(string $code): ?Locale
    {
        if (! Schema::hasTable('locales')) {
            return null;
        }

        return Locale::query()->where('locale', $code)->first();
    }

    public function default(): ?Locale
    {
        return $this->available()
            ->sortByDesc('is_default')
            ->first();
    }

    /** @return Collection<int, Locale> */
    public function available(): Collection
    {
        if (! Schema::hasTable('locales')) {
            return new Collection;
        }

        return Locale::query()
            ->where('is_active', true)
            ->whereIn('launch_status', ['ready', 'launched'])
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->orderBy('language_name')
            ->get();
    }

    public function isPublic(Locale $locale): bool
    {
        return $locale->is_active
            && in_array($locale->launch_status, ['ready', 'launched'], true);
    }
}
