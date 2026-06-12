<?php

namespace App\Services\Seo;

use App\Models\Locale;
use App\Models\SeoRecord;
use Illuminate\Support\Collection;

class SeoDiagnosticsService
{
    public function __construct(
        private readonly SeoDuplicateDetector $duplicates,
        private readonly SeoCanonicalAuditService $canonicals,
        private readonly SeoSchemaAuditService $schemas,
        private readonly HreflangReadinessService $hreflang,
        private readonly SitemapReadinessService $sitemap,
        private readonly RobotsSafetyService $robots,
    ) {}

    /** @param array<string, mixed> $filters */
    public function dashboard(array $filters = []): array
    {
        $issues = collect()
            ->merge($this->missingMetadataIssues())
            ->merge($this->duplicates->duplicates('meta_title')->flatMap(fn (array $duplicate): array => $this->duplicateIssues($duplicate, 'Duplicate meta title'))->all())
            ->merge($this->duplicates->duplicates('meta_description')->flatMap(fn (array $duplicate): array => $this->duplicateIssues($duplicate, 'Duplicate meta description'))->all())
            ->merge($this->missingImageIssues())
            ->merge($this->canonicals->issues())
            ->merge($this->noindexIssues())
            ->merge($this->schemas->issues())
            ->merge($this->slugConflictIssues())
            ->merge($this->hreflang->matrix()['conflicts'])
            ->values();

        $filtered = $this->filterIssues($issues, $filters);

        return [
            'summary' => $this->summary($issues),
            'coverage' => $this->coverage(),
            'issues' => $filtered,
            'allIssues' => $issues,
            'hreflang' => $this->hreflang->matrix(),
            'sitemap' => $this->sitemap->statuses(),
            'robots' => $this->robots->readiness(),
            'filters' => [
                'severity' => $filters['severity'] ?? 'all',
                'issue' => $filters['issue'] ?? 'all',
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function missingMetadataIssues(): array
    {
        return SeoRecord::query()
            ->with('locale')
            ->where(fn ($query) => $query
                ->whereNull('meta_title')
                ->orWhere('meta_title', '')
                ->orWhereNull('meta_description')
                ->orWhere('meta_description', ''))
            ->get()
            ->map(fn (SeoRecord $record): array => [
                'severity' => 'critical',
                'type' => 'missing_metadata',
                'title' => 'Missing metadata',
                'message' => 'Title or description is empty.',
                'record' => $record,
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function duplicateIssues(array $duplicate, string $title): array
    {
        return $duplicate['records']->map(fn (SeoRecord $record): array => [
            'severity' => 'warning',
            'type' => $duplicate['field'] === 'meta_title' ? 'duplicate_title' : 'duplicate_description',
            'title' => $title,
            'message' => 'This value appears on '.$duplicate['count'].' SEO records.',
            'record' => $record,
        ])->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function missingImageIssues(): array
    {
        return SeoRecord::query()
            ->with('locale')
            ->whereNull('og_image_media_id')
            ->get()
            ->map(fn (SeoRecord $record): array => [
                'severity' => 'notice',
                'type' => 'missing_og_image',
                'title' => 'Missing OG image',
                'message' => 'Social previews will look weaker without an image.',
                'record' => $record,
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function noindexIssues(): array
    {
        return SeoRecord::query()
            ->with('locale')
            ->where('robots_index', false)
            ->get()
            ->map(fn (SeoRecord $record): array => [
                'severity' => 'warning',
                'type' => 'noindex_risk',
                'title' => 'Noindex risk',
                'message' => 'This target is excluded from search indexing.',
                'record' => $record,
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function slugConflictIssues(): array
    {
        $records = SeoRecord::query()->with('locale')->whereNotNull('canonical_url')->get();

        return $records
            ->groupBy(fn (SeoRecord $record): string => (string) parse_url((string) $record->canonical_url, PHP_URL_PATH))
            ->filter(fn (Collection $group, string $path): bool => filled($path) && $group->count() > 1)
            ->flatMap(fn (Collection $group): array => $group->map(fn (SeoRecord $record): array => [
                'severity' => 'warning',
                'type' => 'slug_conflict',
                'title' => 'Canonical path conflict',
                'message' => 'Multiple SEO targets share the same canonical path.',
                'record' => $record,
            ])->all())
            ->values()
            ->all();
    }

    /** @param Collection<int, array<string, mixed>> $issues */
    private function filterIssues(Collection $issues, array $filters): Collection
    {
        return $issues
            ->when(($filters['severity'] ?? 'all') !== 'all', fn (Collection $items): Collection => $items->where('severity', $filters['severity']))
            ->when(($filters['issue'] ?? 'all') !== 'all', fn (Collection $items): Collection => $items->where('type', $filters['issue']))
            ->values()
            ->take(24);
    }

    /** @param Collection<int, array<string, mixed>> $issues */
    private function summary(Collection $issues): array
    {
        return [
            'total' => $issues->count(),
            'critical' => $issues->where('severity', 'critical')->count(),
            'warning' => $issues->where('severity', 'warning')->count(),
            'notice' => $issues->where('severity', 'notice')->count(),
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    private function coverage(): Collection
    {
        $records = SeoRecord::query()->with('locale')->get();

        return Locale::query()
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->orderBy('language_name')
            ->get()
            ->map(function (Locale $locale) use ($records): array {
                $localeRecords = $records->where('locale_id', $locale->id);
                $ready = $localeRecords->filter(fn (SeoRecord $record): bool => filled($record->meta_title) && filled($record->meta_description))->count();

                return [
                    'locale' => $locale,
                    'records' => $localeRecords->count(),
                    'ready' => $ready,
                    'score' => $localeRecords->count() > 0 ? (int) round(($ready / $localeRecords->count()) * 100) : 0,
                ];
            });
    }
}
