<?php

namespace App\Services\Translations;

use App\Models\Locale;
use App\Models\TranslationSource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TranslationEditorService
{
    /** @return Collection<int, Locale> */
    public function activeTargetLocales(): Collection
    {
        return Locale::query()
            ->where('is_active', true)
            ->where('locale', '!=', 'en')
            ->orderBy('sort_order')
            ->get();
    }

    /** @param array<string, mixed> $filters */
    public function sources(Locale $locale, array $filters): LengthAwarePaginator
    {
        return TranslationSource::query()
            ->where('is_active', true)
            ->with(['values' => fn ($query) => $query->where('locale_id', $locale->id)])
            ->when(($filters['group'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('group_key', $filters['group']))
            ->when(($filters['requirement'] ?? 'all') === 'required', fn (Builder $query) => $query->where('is_required', true))
            ->when(($filters['requirement'] ?? 'all') === 'optional', fn (Builder $query) => $query->where('is_required', false))
            ->when(($filters['status'] ?? 'all') !== 'all', function (Builder $query) use ($filters, $locale): void {
                $status = $filters['status'];
                if ($status === 'missing') {
                    $query->whereDoesntHave('values', fn (Builder $value) => $value->where('locale_id', $locale->id)->whereNotNull('value')->where('value', '!=', ''));
                } else {
                    $query->whereHas('values', fn (Builder $value) => $value->where('locale_id', $locale->id)->where('status', $status));
                }
            })
            ->when(($filters['missing'] ?? 'all') === 'missing', fn (Builder $query) => $query
                ->whereDoesntHave('values', fn (Builder $value) => $value->where('locale_id', $locale->id)->whereNotNull('value')->where('value', '!=', '')))
            ->when(filled($filters['q'] ?? null), function (Builder $query) use ($filters, $locale): void {
                $search = (string) $filters['q'];
                $query->where(fn (Builder $inner) => $inner
                    ->where('translation_key', 'like', '%'.$search.'%')
                    ->orWhere('source_value', 'like', '%'.$search.'%')
                    ->orWhereHas('values', fn (Builder $value) => $value
                        ->where('locale_id', $locale->id)
                        ->where('value', 'like', '%'.$search.'%')));
            })
            ->orderBy('group_key')
            ->orderBy('sort_order')
            ->paginate((int) ($filters['per_page'] ?? 12))
            ->withQueryString();
    }
}
