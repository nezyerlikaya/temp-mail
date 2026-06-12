<?php

namespace App\Services\Sections;

use App\Models\BlogCategory;
use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class SectionEditorService
{
    public function __construct(
        private readonly SectionStore $store,
        private readonly SectionItemService $items,
        private readonly FaqQualityService $faqQuality,
    ) {}

    /** @return array<string, mixed> */
    public function data(?Section $section, ?User $actor): array
    {
        $section?->loadMissing(['locale', 'creator', 'updater', 'items']);

        return [
            'locales' => $this->store->locales(),
            'types' => $this->store->types(),
            'placements' => $this->store->placements(),
            'statuses' => $this->store->editorStatuses(),
            'visibilities' => $this->store->visibilities(),
            'deviceVisibilities' => $this->store->deviceVisibilities(),
            'itemStatuses' => $this->items->statuses(),
            'items' => $section ? $this->items->editableItems($section) : collect(),
            'faqQuality' => $section && $section->section_type === 'faq'
                ? $this->faqQuality->forSection($section)
                : null,
            'blogCategories' => Schema::hasTable('blog_categories')
                ? BlogCategory::query()->with('locale')->orderBy('name')->get()
                : collect(),
            'canUpdateItems' => $actor?->can('admin.sections-studio.items.update') ?? false,
            'canReorder' => $actor?->can('admin.sections-studio.reorder') ?? false,
            'canActivate' => $actor?->can('admin.sections-studio.activate') ?? false,
            'canHide' => $actor?->can('admin.sections-studio.hide') ?? false,
            'canTrash' => $actor?->can('admin.sections-studio.trash') ?? false,
            'canRestore' => $actor?->can('admin.sections-studio.restore') ?? false,
            'canDelete' => $actor?->can('admin.sections-studio.delete') ?? false,
            'canPreview' => $actor?->can('admin.sections-studio.preview') ?? false,
        ];
    }
}
