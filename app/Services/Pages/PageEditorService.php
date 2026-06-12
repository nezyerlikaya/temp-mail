<?php

namespace App\Services\Pages;

use App\Models\Page;
use App\Models\User;
use App\Services\Media\MediaPickerSearchService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PageEditorService
{
    public function __construct(
        private readonly PageStore $store,
        private readonly MediaPickerSearchService $media,
    ) {}

    /** @return array<string, mixed> */
    public function data(?Page $page, ?User $actor): array
    {
        $mediaLibraryReady = $this->mediaLibraryReady();

        return [
            'locales' => $this->store->locales(),
            'pageTypes' => $this->store->pageTypes(),
            'statuses' => $this->store->editorStatuses(),
            'readinessOptions' => $this->store->contentReadinessOptions(),
            'mediaLibraryReady' => $mediaLibraryReady,
            'mediaAssets' => $mediaLibraryReady ? $this->media->options(['type' => 'image'], 18) : [],
            'selectedMedia' => $mediaLibraryReady ? $this->media->option($page?->featuredMedia) : null,
            'canSelectMedia' => $mediaLibraryReady && ($actor?->can('admin.media-library.select') ?? false),
            'canUploadMedia' => $mediaLibraryReady && ($actor?->can('admin.media-library.upload-through-picker') ?? false),
            'canPublish' => $actor?->can('admin.page-studio.publish') ?? false,
            'canHide' => $actor?->can('admin.page-studio.hide') ?? false,
            'canTrash' => $actor?->can('admin.page-studio.trash') ?? false,
            'canRestore' => $actor?->can('admin.page-studio.restore') ?? false,
            'canDelete' => $actor?->can('admin.page-studio.delete') ?? false,
            'canPreview' => $actor?->can('admin.page-studio.preview') ?? false,
        ];
    }

    private function mediaLibraryReady(): bool
    {
        try {
            return Route::has('admin.media-library.picker')
                && Schema::hasTable('media_assets')
                && Schema::hasTable('media_usages');
        } catch (Throwable) {
            return false;
        }
    }
}
