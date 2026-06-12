<?php

namespace App\Services\Seo;

use App\Models\SeoRecord;

class SeoHealthService
{
    public function __construct(private readonly SeoTargetRegistry $targets) {}

    /** @return array<string, mixed> */
    public function summary(): array
    {
        $records = SeoRecord::query()->get();
        $targetCount = $this->targets->targets()->count();
        $missingMetadata = $records->filter(fn (SeoRecord $record): bool => blank($record->meta_title) || blank($record->meta_description))->count();
        $noindex = $records->where('robots_index', false)->count();
        $sitemap = $records->where('include_in_sitemap', true)->count();

        return [
            'target_count' => $targetCount,
            'record_count' => $records->count(),
            'coverage' => $targetCount > 0 ? (int) round(($records->count() / $targetCount) * 100) : 0,
            'missing_metadata' => $missingMetadata,
            'noindex' => $noindex,
            'sitemap' => $sitemap,
            'issues' => $this->issues($targetCount, $records->count(), $missingMetadata),
        ];
    }

    /** @return array<int, string> */
    private function issues(int $targetCount, int $recordCount, int $missingMetadata): array
    {
        return collect([
            $recordCount < $targetCount ? 'Some language and target pairs do not have SEO records yet.' : null,
            $missingMetadata > 0 ? 'Some SEO records are missing title or description metadata.' : null,
        ])->filter()->values()->all();
    }
}
