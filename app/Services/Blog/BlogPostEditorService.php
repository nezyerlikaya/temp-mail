<?php

namespace App\Services\Blog;

use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;
use App\Services\Media\MediaPickerSearchService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

class BlogPostEditorService
{
    public function __construct(
        private readonly BlogPostStore $store,
        private readonly MediaPickerSearchService $media,
    ) {}

    /** @return array<string, mixed> */
    public function data(?BlogPost $post, ?User $actor): array
    {
        $mediaLibraryReady = $this->mediaLibraryReady();
        $post?->loadMissing(['featuredMedia', 'tags', 'category', 'locale', 'author']);

        return [
            'locales' => $this->store->locales(),
            'categories' => $this->store->categories(),
            'tags' => BlogTag::query()->with('locale')->orderBy('name')->get(),
            'statuses' => $this->store->editorStatuses(),
            'readinessOptions' => $this->store->contentReadinessOptions(),
            'mediaLibraryReady' => $mediaLibraryReady,
            'mediaAssets' => $mediaLibraryReady ? $this->media->options(['type' => 'image'], 18) : [],
            'selectedMedia' => $mediaLibraryReady ? $this->media->option($post?->featuredMedia) : null,
            'selectedTags' => $post?->tags->pluck('id')->map(fn (int $id): string => (string) $id)->all() ?? [],
            'canSelectMedia' => $mediaLibraryReady && ($actor?->can('admin.media-library.select') ?? false),
            'canUploadMedia' => $mediaLibraryReady && ($actor?->can('admin.media-library.upload-through-picker') ?? false),
            'canPublish' => $actor?->can('admin.blog-studio.publish') ?? false,
            'canHide' => $actor?->can('admin.blog-studio.publish') ?? false,
            'canTrash' => $actor?->can('admin.blog-studio.trash') ?? false,
            'canRestore' => $actor?->can('admin.blog-studio.trash') ?? false,
            'canPreview' => $actor?->can('admin.blog-studio.view') ?? false,
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
