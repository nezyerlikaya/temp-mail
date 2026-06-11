<?php

namespace App\Services\Media;

use App\Models\MediaAsset;

class AvatarMediaResolver
{
    public function __construct(
        private readonly MediaQualityService $quality,
        private readonly MediaUrlResolver $urls,
    ) {}

    /** @return array{eligible: bool, category_ready: bool, square: bool, recommendation: string, crop_hook: bool, initials_fallback: bool, color_fallback: bool, url: string|null} */
    public function resolve(MediaAsset $asset): array
    {
        $metadata = $this->quality->metadata($asset);
        $square = $asset->width !== null
            && $asset->height !== null
            && $asset->width === $asset->height;

        return [
            'eligible' => $this->quality->isImage($asset),
            'category_ready' => $asset->type === 'avatar',
            'square' => $square,
            'recommendation' => $square
                ? 'Square image ready'
                : 'Use a square image, ideally 512 x 512 pixels or larger.',
            'crop_hook' => ! $square && $metadata['width'] !== null,
            'initials_fallback' => true,
            'color_fallback' => true,
            'url' => $this->quality->isImage($asset) ? $this->urls->url($asset) : null,
        ];
    }
}
