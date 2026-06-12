<?php

namespace App\Services\Seo;

use App\Models\Locale;
use App\Models\SeoRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SeoStore
{
    public function __construct(
        private readonly SeoTargetRegistry $targets,
        private readonly SeoHealthService $health,
    ) {}

    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = SeoRecord::query()
            ->with(['locale', 'ogImage'])
            ->latest();

        if (($filters['locale'] ?? 'all') !== 'all') {
            $query->whereHas('locale', fn ($localeQuery) => $localeQuery->where('locale', (string) $filters['locale']));
        }

        if (($filters['target_type'] ?? 'all') !== 'all') {
            $query->where('target_type', (string) $filters['target_type']);
        }

        if (($filters['missing_metadata'] ?? 'all') === 'missing') {
            $query->where(fn ($missing) => $missing
                ->whereNull('meta_title')
                ->orWhere('meta_title', '')
                ->orWhereNull('meta_description')
                ->orWhere('meta_description', ''));
        }

        if (($filters['robots'] ?? 'all') !== 'all') {
            $query->where('robots_index', ($filters['robots'] ?? 'all') === 'index');
        }

        if (($filters['sitemap'] ?? 'all') !== 'all') {
            $query->where('include_in_sitemap', ($filters['sitemap'] ?? 'all') === 'included');
        }

        return $query->paginate(12)->withQueryString();
    }

    /** @return Collection<int, Locale> */
    public function locales(): Collection
    {
        return Locale::query()->orderByDesc('is_default')->orderBy('sort_order')->orderBy('language_name')->get();
    }

    /** @return array<string, string> */
    public function targetTypes(): array
    {
        return $this->targets->targetTypes();
    }

    /** @return array<string, string> */
    public function changeFrequencies(): array
    {
        return [
            'always' => 'Always',
            'hourly' => 'Hourly',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'yearly' => 'Yearly',
            'never' => 'Never',
        ];
    }

    /** @return array<string, string> */
    public function twitterCards(): array
    {
        return [
            'summary' => 'Summary',
            'summary_large_image' => 'Summary large image',
        ];
    }

    /** @return array<string, mixed> */
    public function summary(): array
    {
        return $this->health->summary();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function targetQueue(?string $localeCode = null): Collection
    {
        $locale = $localeCode && $localeCode !== 'all'
            ? Locale::query()->where('locale', $localeCode)->first()
            : null;

        return $this->targets->targets($locale)
            ->take(18)
            ->values();
    }
}
