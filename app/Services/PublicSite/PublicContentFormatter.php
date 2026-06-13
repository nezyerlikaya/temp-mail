<?php

namespace App\Services\PublicSite;

use App\Models\MediaAsset;
use App\Services\Mailboxes\MessageSanitizer;
use App\Services\Media\MediaUrlResolver;

class PublicContentFormatter
{
    public function __construct(
        private readonly MessageSanitizer $sanitizer,
        private readonly MediaUrlResolver $media,
    ) {}

    public function html(?string $content): string
    {
        return $this->sanitizer->sanitize($content) ?? '';
    }

    /** @return array<string, mixed>|null */
    public function image(?MediaAsset $asset): ?array
    {
        if (! $asset || $asset->status !== 'active' || ! str_starts_with($asset->mime_type, 'image/')) {
            return null;
        }

        return [
            'url' => $this->media->url($asset),
            'alt' => $asset->alt_text ?: $asset->title ?: '',
            'width' => $asset->width,
            'height' => $asset->height,
        ];
    }
}
