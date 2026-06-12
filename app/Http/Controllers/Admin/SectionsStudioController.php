<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Sections\ActivateSectionAction;
use App\Actions\Sections\CreateSectionAction;
use App\Actions\Sections\CreateSectionItemAction;
use App\Actions\Sections\DeleteSectionAction;
use App\Actions\Sections\DeleteSectionItemAction;
use App\Actions\Sections\HideSectionAction;
use App\Actions\Sections\ReorderSectionItemsAction;
use App\Actions\Sections\ReorderSectionsAction;
use App\Actions\Sections\RestoreSectionAction;
use App\Actions\Sections\TrashSectionAction;
use App\Actions\Sections\UpdateSectionAction;
use App\Actions\Sections\UpdateSectionItemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sections\ActivateSectionRequest;
use App\Http\Requests\Sections\DeleteSectionItemRequest;
use App\Http\Requests\Sections\DeleteSectionRequest;
use App\Http\Requests\Sections\HideSectionRequest;
use App\Http\Requests\Sections\ReorderSectionItemsRequest;
use App\Http\Requests\Sections\ReorderSectionsRequest;
use App\Http\Requests\Sections\RestoreSectionRequest;
use App\Http\Requests\Sections\SectionFilterRequest;
use App\Http\Requests\Sections\StoreSectionItemRequest;
use App\Http\Requests\Sections\StoreSectionRequest;
use App\Http\Requests\Sections\TrashSectionRequest;
use App\Http\Requests\Sections\UpdateSectionItemRequest;
use App\Http\Requests\Sections\UpdateSectionRequest;
use App\Models\Section;
use App\Models\SectionItem;
use App\Services\Sections\SectionEditorService;
use App\Services\Sections\SectionPreviewService;
use App\Services\Sections\SectionRenderService;
use App\Services\Sections\SectionSearchService;
use App\Services\Sections\SectionSeoReadinessService;
use App\Services\Sections\SectionStore;
use App\Services\Sections\SectionThemeContractService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SectionsStudioController extends Controller
{
    public function index(SectionFilterRequest $request, SectionStore $store, SectionSearchService $search, SectionPreviewService $preview): View
    {
        $filters = [
            'q' => (string) $request->query('q', ''),
            'locale_id' => (string) $request->query('locale_id', 'all'),
            'section_type' => (string) $request->query('section_type', 'all'),
            'placement' => (string) $request->query('placement', 'all'),
            'status' => (string) $request->query('status', 'all'),
        ];

        return view('dashboard.sections-studio.index', [
            'adminUser' => $request->user(),
            'sections' => $sections = $search->search([...$request->validated(), ...$filters]),
            'summary' => $store->summary(),
            'filters' => $filters,
            'locales' => $store->locales(),
            'types' => $store->types(),
            'placements' => $store->placements(),
            'statuses' => $store->statuses(),
            'editorStatuses' => $store->editorStatuses(),
            'visibilities' => $store->visibilities(),
            'deviceVisibilities' => $store->deviceVisibilities(),
            'canCreateSection' => $request->user()?->can('admin.sections-studio.create') ?? false,
            'canUpdateSection' => $request->user()?->can('admin.sections-studio.update') ?? false,
            'canReorderSection' => $request->user()?->can('admin.sections-studio.reorder') ?? false,
            'canPreviewSection' => $request->user()?->can('admin.sections-studio.preview') ?? false,
            'previewUrls' => $sections->getCollection()->mapWithKeys(fn (Section $section): array => [
                $section->id => $preview->previewUrl($section),
            ])->all(),
        ]);
    }

    public function create(SectionFilterRequest $request, SectionEditorService $editor): View
    {
        $request->user()?->can('admin.sections-studio.create') || abort(403);

        return view('dashboard.sections-studio.create', [
            'adminUser' => $request->user(),
            'section' => null,
            'editor' => $editor->data(null, $request->user()),
        ]);
    }

    public function store(StoreSectionRequest $request, CreateSectionAction $create): RedirectResponse
    {
        $section = $create->handle($request->user(), $request->validated());

        return redirect()
            ->route('admin.sections-studio.edit', $section)
            ->with('status', 'Section created.');
    }

    public function edit(
        SectionFilterRequest $request,
        Section $section,
        SectionEditorService $editor,
        SectionRenderService $render,
        SectionSeoReadinessService $seo,
        SectionThemeContractService $themes,
        SectionPreviewService $preview,
    ): View {
        $request->user()?->can('admin.sections-studio.update') || abort(403);
        $section->load(['locale', 'creator', 'updater', 'items']);

        return view('dashboard.sections-studio.edit', [
            'adminUser' => $request->user(),
            'section' => $section,
            'editor' => $editor->data($section, $request->user()),
            'renderReadiness' => $render->readiness($section),
            'seoReadiness' => $seo->forSection($section),
            'themeContracts' => $themes->readiness(),
            'preview' => $preview->readiness($section),
        ]);
    }

    public function update(UpdateSectionRequest $request, Section $section, UpdateSectionAction $update): RedirectResponse
    {
        $update->handle($request->user(), $section, $request->validated());

        return redirect()
            ->route('admin.sections-studio.edit', $section)
            ->with('status', 'Section updated.');
    }

    public function storeItem(StoreSectionItemRequest $request, Section $section, CreateSectionItemAction $create): RedirectResponse
    {
        $create->handle($request->user(), $section, $request->validated());

        return redirect()->route('admin.sections-studio.edit', $section)->with('status', 'Section item added.');
    }

    public function updateItem(UpdateSectionItemRequest $request, Section $section, SectionItem $sectionItem, UpdateSectionItemAction $update): RedirectResponse
    {
        $update->handle($request->user(), $section, $sectionItem, $request->validated());

        return redirect()->route('admin.sections-studio.edit', $section)->with('status', 'Section item updated.');
    }

    public function destroyItem(DeleteSectionItemRequest $request, Section $section, SectionItem $sectionItem, DeleteSectionItemAction $delete): RedirectResponse
    {
        $delete->handle($request->user(), $section, $sectionItem);

        return redirect()->route('admin.sections-studio.edit', $section)->with('status', 'Section item removed.');
    }

    public function reorder(ReorderSectionsRequest $request, ReorderSectionsAction $reorder): RedirectResponse
    {
        $reorder->handle(
            $request->user(),
            $request->integer('locale_id'),
            (string) $request->validated('placement'),
            $request->validated('order'),
        );

        return back()->with('status', 'Section order updated.');
    }

    public function reorderItems(ReorderSectionItemsRequest $request, Section $section, ReorderSectionItemsAction $reorder): RedirectResponse
    {
        $reorder->handle($section, $request->validated('order'));

        return redirect()->route('admin.sections-studio.edit', $section)->with('status', 'Item order updated.');
    }

    public function activate(ActivateSectionRequest $request, Section $section, ActivateSectionAction $activate): RedirectResponse
    {
        $activate->handle($request->user(), $section);

        return redirect()->route('admin.sections-studio.edit', $section)->with('status', 'Section activated.');
    }

    public function hide(HideSectionRequest $request, Section $section, HideSectionAction $hide): RedirectResponse
    {
        $hide->handle($request->user(), $section);

        return redirect()->route('admin.sections-studio.edit', $section)->with('status', 'Section hidden.');
    }

    public function trash(TrashSectionRequest $request, Section $section, TrashSectionAction $trash): RedirectResponse
    {
        $trash->handle($request->user(), $section);

        return redirect()->route('admin.sections-studio.index', ['status' => 'trashed'])->with('status', 'Section moved to trash.');
    }

    public function restore(RestoreSectionRequest $request, Section $section, RestoreSectionAction $restore): RedirectResponse
    {
        $restore->handle($request->user(), $section);

        return redirect()->route('admin.sections-studio.edit', $section)->with('status', 'Section restored as draft.');
    }

    public function destroy(DeleteSectionRequest $request, Section $section, DeleteSectionAction $delete): RedirectResponse
    {
        $delete->handle($request->user(), $section);

        return redirect()->route('admin.sections-studio.index', ['status' => 'trashed'])->with('status', 'Permanent delete readiness completed.');
    }

    public function preview(SectionFilterRequest $request, Section $section, SectionRenderService $render, SectionSeoReadinessService $seo, SectionThemeContractService $themes): View
    {
        $request->user()?->can('admin.sections-studio.preview') || abort(403);

        return view('dashboard.sections-studio.preview', [
            'adminUser' => $request->user(),
            'section' => $section->load(['locale', 'items']),
            'renderReadiness' => $render->readiness($section),
            'seoReadiness' => $seo->forSection($section),
            'themeContracts' => $themes->readiness(),
        ]);
    }
}
