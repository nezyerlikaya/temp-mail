<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Sections\CreateSectionAction;
use App\Actions\Sections\UpdateSectionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sections\SectionFilterRequest;
use App\Http\Requests\Sections\StoreSectionRequest;
use App\Http\Requests\Sections\UpdateSectionRequest;
use App\Models\Section;
use App\Services\Sections\SectionSearchService;
use App\Services\Sections\SectionStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SectionsStudioController extends Controller
{
    public function index(SectionFilterRequest $request, SectionStore $store, SectionSearchService $search): View
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
            'sections' => $search->search([...$request->validated(), ...$filters]),
            'summary' => $store->summary(),
            'filters' => $filters,
            'locales' => $store->locales(),
            'types' => $store->types(),
            'placements' => $store->placements(),
            'statuses' => $store->statuses(),
            'editorStatuses' => $store->editorStatuses(),
            'visibilities' => $store->visibilities(),
            'canCreateSection' => $request->user()?->can('admin.sections-studio.create') ?? false,
            'canUpdateSection' => $request->user()?->can('admin.sections-studio.update') ?? false,
        ]);
    }

    public function store(StoreSectionRequest $request, CreateSectionAction $create): RedirectResponse
    {
        $section = $create->handle($request->user(), $request->validated());

        return redirect()
            ->route('admin.sections-studio.index', ['section_type' => $section->section_type])
            ->with('status', 'Section foundation created.');
    }

    public function update(UpdateSectionRequest $request, Section $section, UpdateSectionAction $update): RedirectResponse
    {
        $update->handle($request->user(), $section, $request->validated());

        return redirect()
            ->route('admin.sections-studio.index', ['section_type' => $section->section_type])
            ->with('status', 'Section foundation updated.');
    }
}
