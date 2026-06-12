<?php

namespace App\Actions\Seo;

use App\Models\SeoRecord;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Seo\SeoMediaService;
use App\Services\Seo\SeoVersionService;
use Illuminate\Support\Facades\DB;

class UpdateSeoRecordAction
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly SeoMediaService $media,
        private readonly SeoVersionService $versions,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, SeoRecord $record, array $payload): SeoRecord
    {
        return DB::transaction(function () use ($actor, $record, $payload): SeoRecord {
            $previousOgImageId = $record->og_image_media_id;
            $previousTwitterImageId = $record->twitter_image_media_id;
            $this->versions->capture($record, $actor);

            $payload['updated_by'] = $actor->id;
            $record->update($payload);
            $this->media->sync($actor, $record->refresh(), $previousOgImageId, $previousTwitterImageId);

            $this->audit->record('seo.record_updated', $actor, null, [
                'seo_record_id' => $record->id,
                'locale_id' => $record->locale_id,
                'target_type' => $record->target_type,
                'target_key' => $record->target_key,
                'robots_index' => $record->robots_index,
                'include_in_sitemap' => $record->include_in_sitemap,
            ], ['module' => 'seo', 'action' => 'Update SEO record', 'target' => $record]);

            return $record->refresh();
        });
    }
}
