<?php

namespace App\Services\Media;

use App\Models\MediaAsset;

class MediaQualityService
{
    private const OVERSIZED_BYTES = 2 * 1024 * 1024;

    /** @return array<int, array{code: string, label: string, message: string, severity: string}> */
    public function warnings(MediaAsset $asset): array
    {
        $warnings = [];

        if ($this->isImage($asset) && blank($asset->alt_text)) {
            $warnings[] = [
                'code' => 'missing_alt',
                'label' => 'Missing alt text',
                'message' => 'Add concise alternative text before using this image in published content.',
                'severity' => 'warning',
            ];
        }

        if ($asset->size_bytes > self::OVERSIZED_BYTES) {
            $warnings[] = [
                'code' => 'oversized',
                'label' => 'Large file',
                'message' => 'This file is larger than 2 MB and may slow public pages.',
                'severity' => 'warning',
            ];
        }

        if ($this->isImage($asset) && (! $asset->width || ! $asset->height)) {
            $warnings[] = [
                'code' => 'missing_dimensions',
                'label' => 'Dimensions unavailable',
                'message' => 'Width and height could not be detected for this asset.',
                'severity' => 'info',
            ];
        }

        return $warnings;
    }

    /** @return array{width: int|null, height: int|null, aspect_ratio: string|null, aspect_ratio_value: float|null} */
    public function metadata(MediaAsset $asset): array
    {
        $ratio = $asset->width && $asset->height
            ? $asset->width / $asset->height
            : null;

        return [
            'width' => $asset->width,
            'height' => $asset->height,
            'aspect_ratio' => $ratio ? $this->ratioLabel($asset->width, $asset->height) : null,
            'aspect_ratio_value' => $ratio ? round($ratio, 3) : null,
        ];
    }

    public function isImage(MediaAsset $asset): bool
    {
        return str_starts_with($asset->mime_type, 'image/');
    }

    private function ratioLabel(int $width, int $height): string
    {
        $divisor = $this->greatestCommonDivisor($width, $height);

        return (int) ($width / $divisor).':'.(int) ($height / $divisor);
    }

    private function greatestCommonDivisor(int $left, int $right): int
    {
        while ($right !== 0) {
            [$left, $right] = [$right, $left % $right];
        }

        return max(1, $left);
    }
}
