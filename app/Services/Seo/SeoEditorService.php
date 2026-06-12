<?php

namespace App\Services\Seo;

use App\Models\SeoRecord;
use App\Models\User;
use App\Services\Media\MediaPickerSearchService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class SeoEditorService
{
    public function __construct(
        private readonly SeoStore $store,
        private readonly SeoTargetRegistry $targets,
        private readonly SeoPreviewService $preview,
        private readonly MediaPickerSearchService $media,
    ) {}

    /** @return array<string, mixed> */
    public function data(?SeoRecord $record, ?User $actor): array
    {
        $record?->loadMissing(['locale', 'ogImage', 'twitterImage']);
        $mediaReady = $this->mediaReady();

        return [
            'locales' => $this->store->locales(),
            'targetTypes' => $this->store->targetTypes(),
            'targets' => $this->targets->targets()->values(),
            'changeFrequencies' => $this->store->changeFrequencies(),
            'twitterCards' => $this->store->twitterCards(),
            'schemaTypes' => [
                '' => 'None',
                'WebSite' => 'WebSite',
                'WebPage' => 'WebPage',
                'Article' => 'Article',
                'CollectionPage' => 'CollectionPage',
                'FAQPage' => 'FAQPage',
                'BreadcrumbList' => 'BreadcrumbList',
            ],
            'mediaReady' => $mediaReady,
            'mediaAssets' => $mediaReady ? $this->media->options(['type' => 'seo'], 18) : [],
            'selectedOgImage' => $mediaReady ? $this->media->option($record?->ogImage) : null,
            'selectedTwitterImage' => $mediaReady ? $this->media->option($record?->twitterImage) : null,
            'canSelectMedia' => $mediaReady && ($actor?->can('admin.seo-growth-center.media') ?? false),
            'canUploadMedia' => $mediaReady && ($actor?->can('admin.media-library.upload-through-picker') ?? false),
            'canUpdateSchema' => $actor?->can('admin.seo-growth-center.schema') ?? false,
            'preview' => $record ? $this->preview->preview($record) : null,
        ];
    }

    private function mediaReady(): bool
    {
        return Route::has('admin.media-library.picker')
            && Schema::hasTable('media_assets')
            && Schema::hasTable('media_usages');
    }
}
