<?php

namespace App\Services\Seo;

use App\Models\SeoRecord;
use Illuminate\Support\Collection;

class SitemapReadinessService
{
    /** @return array<int, array<string, mixed>> */
    public function statuses(): array
    {
        $records = SeoRecord::query()->with('locale')->get();

        return [
            $this->status('Language sitemap', $records->where('target_type', 'language_landing')),
            $this->status('Page sitemap', $records->where('target_type', 'page')),
            $this->status('Blog sitemap', $records->whereIn('target_type', ['blog_post', 'blog_category', 'blog_tag'])),
            $this->status('Media sitemap readiness', $records->filter(fn (SeoRecord $record): bool => filled($record->og_image_media_id) || filled($record->twitter_image_media_id))),
        ];
    }

    /** @param Collection<int, SeoRecord> $records */
    private function status(string $label, Collection $records): array
    {
        $ready = $records->filter(fn (SeoRecord $record): bool => $record->include_in_sitemap && filled($record->canonical_url))->count();
        $total = $records->count();

        return [
            'label' => $label,
            'ready' => $ready,
            'total' => $total,
            'state' => $total === 0 ? 'empty' : ($ready === $total ? 'ready' : 'attention'),
        ];
    }
}
