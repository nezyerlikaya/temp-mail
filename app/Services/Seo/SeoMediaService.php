<?php

namespace App\Services\Seo;

use App\Actions\Media\AttachMediaUsageAction;
use App\Actions\Media\DetachMediaUsageAction;
use App\Models\MediaAsset;
use App\Models\SeoRecord;
use App\Models\User;

class SeoMediaService
{
    public function __construct(
        private readonly AttachMediaUsageAction $attach,
        private readonly DetachMediaUsageAction $detach,
    ) {}

    public function sync(User $actor, SeoRecord $record, mixed $previousOgImageId, mixed $previousTwitterImageId): void
    {
        $this->syncSlot($actor, $record, 'og_image_media_id', $record->og_image_media_id, $previousOgImageId, 'SEO Open Graph image');
        $this->syncSlot($actor, $record, 'twitter_image_media_id', $record->twitter_image_media_id, $previousTwitterImageId, 'SEO Twitter/X image');
    }

    private function syncSlot(User $actor, SeoRecord $record, string $slot, mixed $currentId, mixed $previousId, string $label): void
    {
        if ((int) $currentId === (int) $previousId) {
            return;
        }

        $usage = [
            'module' => 'seo',
            'usage_context' => 'seo_growth_center',
            'slot' => $slot,
            'usable_type' => SeoRecord::class,
            'usable_id' => (string) $record->id,
        ];

        $this->detach->handle($actor, $usage);

        $asset = $currentId ? MediaAsset::query()->find($currentId) : null;
        if ($asset) {
            $this->attach->handle($actor, $asset, [
                ...$usage,
                'label' => $label,
                'metadata' => [
                    'target_type' => $record->target_type,
                    'target_key' => $record->target_key,
                    'locale_id' => $record->locale_id,
                ],
            ]);
        }
    }
}
