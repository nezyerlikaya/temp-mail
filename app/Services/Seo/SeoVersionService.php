<?php

namespace App\Services\Seo;

use App\Models\SeoRecord;
use App\Models\SeoVersion;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Collection;

class SeoVersionService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function capture(SeoRecord $record, User $actor, string $reason = 'manual_update'): SeoVersion
    {
        return SeoVersion::query()->create([
            'seo_record_id' => $record->id,
            'created_by' => $actor->id,
            'snapshot' => $record->only([
                'meta_title',
                'meta_description',
                'canonical_url',
                'robots_index',
                'robots_follow',
                'include_in_sitemap',
                'sitemap_priority',
                'sitemap_change_frequency',
                'og_title',
                'og_description',
                'og_image_media_id',
                'twitter_card',
                'twitter_title',
                'twitter_description',
                'twitter_image_media_id',
                'schema_type',
                'schema_json',
                'breadcrumb_title',
            ]),
            'reason' => $reason,
        ]);
    }

    /** @return Collection<int, SeoVersion> */
    public function latest(?SeoRecord $record = null): Collection
    {
        return SeoVersion::query()
            ->with(['record.locale', 'creator'])
            ->when($record, fn ($query) => $query->where('seo_record_id', $record->id))
            ->latest()
            ->limit(8)
            ->get();
    }

    public function rollback(User $actor, SeoVersion $version): SeoRecord
    {
        $record = $version->record;
        $record->update([...$version->snapshot, 'updated_by' => $actor->id]);

        $this->audit->record('seo.version_rollback_ready', $actor, null, [
            'seo_version_id' => $version->id,
            'seo_record_id' => $record->id,
        ], ['module' => 'seo', 'action' => 'Review SEO rollback', 'target' => $record, 'severity' => 'warning']);

        return $record->refresh();
    }
}
