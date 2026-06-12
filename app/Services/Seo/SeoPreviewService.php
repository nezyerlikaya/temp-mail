<?php

namespace App\Services\Seo;

use App\Models\SeoRecord;
use App\Services\Media\MediaPickerSearchService;

class SeoPreviewService
{
    public function __construct(private readonly MediaPickerSearchService $media) {}

    /** @return array<string, mixed> */
    public function preview(SeoRecord $record): array
    {
        $title = $record->meta_title ?: 'Meta title pending';
        $description = $record->meta_description ?: 'Meta description pending.';
        $canonical = $record->canonical_url ?: url('/');
        $ogImage = $this->media->option($record->ogImage);

        return [
            'serp' => [
                'desktop' => [
                    'title' => $title,
                    'description' => $description,
                    'url' => $canonical,
                    'title_length' => mb_strlen($title),
                    'description_length' => mb_strlen($description),
                ],
                'mobile' => [
                    'title' => str($title)->limit(56)->toString(),
                    'description' => str($description)->limit(118)->toString(),
                    'url' => $canonical,
                ],
            ],
            'social' => [
                'og_title' => $record->og_title ?: $title,
                'og_description' => $record->og_description ?: $description,
                'og_image' => $ogImage,
                'twitter_card' => $record->twitter_card,
                'twitter_title' => $record->twitter_title ?: $record->og_title ?: $title,
                'twitter_description' => $record->twitter_description ?: $record->og_description ?: $description,
                'twitter_image' => $this->media->option($record->twitterImage),
            ],
            'warnings' => [
                'noindex' => ! $record->robots_index,
                'title_quality' => mb_strlen($title) >= 45 && mb_strlen($title) <= 60,
                'description_quality' => mb_strlen($description) >= 120 && mb_strlen($description) <= 155,
            ],
        ];
    }
}
