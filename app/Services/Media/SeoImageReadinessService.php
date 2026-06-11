<?php

namespace App\Services\Media;

use App\Models\MediaAsset;

class SeoImageReadinessService
{
    public function __construct(private readonly MediaQualityService $quality) {}

    /** @return array{eligible: bool, category_ready: bool, dimensions_ready: bool, alt_ready: bool, size_ready: bool, recommended_dimensions: string, warnings: array<int, string>} */
    public function assess(MediaAsset $asset): array
    {
        $isImage = $this->quality->isImage($asset);
        $dimensionsReady = $asset->width === 1200 && $asset->height === 630;
        $warnings = [];

        if (! $isImage) {
            $warnings[] = 'SEO social images must use an image file.';
        }

        if (! $dimensionsReady) {
            $warnings[] = 'Recommended OG dimensions are 1200 x 630 pixels.';
        }

        if (blank($asset->alt_text)) {
            $warnings[] = 'Add alt text for accessible social previews.';
        }

        if ($asset->size_bytes > 2 * 1024 * 1024) {
            $warnings[] = 'Keep social images at or below 2 MB where possible.';
        }

        return [
            'eligible' => $isImage,
            'category_ready' => $asset->type === 'seo',
            'dimensions_ready' => $dimensionsReady,
            'alt_ready' => filled($asset->alt_text),
            'size_ready' => $asset->size_bytes <= 2 * 1024 * 1024,
            'recommended_dimensions' => '1200 x 630',
            'warnings' => $warnings,
        ];
    }
}
