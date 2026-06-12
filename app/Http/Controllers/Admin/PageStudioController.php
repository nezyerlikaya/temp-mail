<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Pages\CreatePageAction;
use App\Actions\Pages\DeletePageAction;
use App\Actions\Pages\HidePageAction;
use App\Actions\Pages\PublishPageAction;
use App\Actions\Pages\RestorePageAction;
use App\Actions\Pages\TrashPageAction;
use App\Actions\Pages\UpdatePageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pages\DeletePageRequest;
use App\Http\Requests\Pages\HidePageRequest;
use App\Http\Requests\Pages\PageFilterRequest;
use App\Http\Requests\Pages\PublishPageRequest;
use App\Http\Requests\Pages\RestorePageRequest;
use App\Http\Requests\Pages\StorePageRequest;
use App\Http\Requests\Pages\TrashPageRequest;
use App\Http\Requests\Pages\UpdatePageRequest;
use App\Models\Page;
use App\Services\Pages\PageEditorService;
use App\Services\Pages\PageLegalResolver;
use App\Services\Pages\PagePreviewService;
use App\Services\Pages\PageSearchService;
use App\Services\Pages\PageStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageStudioController extends Controller
{
    public function index(PageFilterRequest $request, PageStore $store, PageSearchService $search, PagePreviewService $preview, PageLegalResolver $legal): View
    {
        $filters = [
            'q' => (string) $request->query('q', ''),
            'locale_id' => (string) $request->query('locale_id', 'all'),
            'page_type' => (string) $request->query('page_type', 'all'),
            'status' => (string) $request->query('status', 'all'),
            'author_id' => (string) $request->query('author_id', 'all'),
            'date' => (string) $request->query('date', 'all'),
        ];
        $pages = $search->search([...$request->validated(), ...$filters]);

        return view('dashboard.page-studio.index', [
            'adminUser' => $request->user(),
            'pages' => $pages,
            'summary' => $store->summary(),
            'recent' => $store->recent(),
            'filters' => [
                ...$filters,
            ],
            'locales' => $store->locales(),
            'authors' => $store->authors(),
            'pageTypes' => $store->pageTypes(),
            'statuses' => $store->statuses(),
            'previewUrls' => $search->previewUrls($pages, $preview),
            'legalReadiness' => $search->legalReadiness($pages, $legal),
            'canCreatePage' => $request->user()?->can('admin.page-studio.create') ?? false,
            'canPreviewPage' => $request->user()?->can('admin.page-studio.preview') ?? false,
        ]);
    }

    public function create(PageFilterRequest $request, PageEditorService $editor): View
    {
        $request->user()?->can('admin.page-studio.create') || abort(403);

        return view('dashboard.page-studio.create', [
            'adminUser' => $request->user(),
            'page' => null,
            'editor' => $editor->data(null, $request->user()),
        ]);
    }

    public function store(StorePageRequest $request, CreatePageAction $create): RedirectResponse
    {
        $page = $create->handle($request->user(), $request->validated());

        return redirect()
            ->route('admin.page-studio.edit', $page)
            ->with('status', 'Page foundation created.');
    }

    public function edit(PageFilterRequest $request, Page $page, PageEditorService $editor, PagePreviewService $preview, PageLegalResolver $legal): View
    {
        $request->user()?->can('admin.page-studio.update') || abort(403);
        $page->load(['locale', 'author', 'featuredMedia']);

        return view('dashboard.page-studio.edit', [
            'adminUser' => $request->user(),
            'page' => $page,
            'editor' => $editor->data($page, $request->user()),
            'preview' => $preview->readiness($page),
            'legal' => $legal->readiness($page),
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page, UpdatePageAction $update): RedirectResponse
    {
        $update->handle($request->user(), $page, $request->validated());

        return redirect()
            ->route('admin.page-studio.edit', $page)
            ->with('status', 'Page foundation updated.');
    }

    public function publish(PublishPageRequest $request, Page $page, PublishPageAction $publish): RedirectResponse
    {
        $publish->handle($request->user(), $page);

        return redirect()->route('admin.page-studio.edit', $page)->with('status', 'Page published.');
    }

    public function hide(HidePageRequest $request, Page $page, HidePageAction $hide): RedirectResponse
    {
        $hide->handle($request->user(), $page);

        return redirect()->route('admin.page-studio.edit', $page)->with('status', 'Page hidden.');
    }

    public function trash(TrashPageRequest $request, Page $page, TrashPageAction $trash): RedirectResponse
    {
        $trash->handle($request->user(), $page);

        return redirect()->route('admin.page-studio.index', ['status' => 'trashed'])->with('status', 'Page moved to trash.');
    }

    public function restore(RestorePageRequest $request, Page $page, RestorePageAction $restore): RedirectResponse
    {
        $restore->handle($request->user(), $page);

        return redirect()->route('admin.page-studio.edit', $page)->with('status', 'Page restored as draft.');
    }

    public function destroy(DeletePageRequest $request, Page $page, DeletePageAction $delete): RedirectResponse
    {
        $delete->handle($request->user(), $page);

        return redirect()->route('admin.page-studio.index', ['status' => 'trashed'])->with('status', 'Permanent delete readiness completed.');
    }

    public function preview(PageFilterRequest $request, Page $page, PagePreviewService $preview, PageLegalResolver $legal): View
    {
        $request->user()?->can('admin.page-studio.preview') || abort(403);

        return view('dashboard.page-studio.preview', [
            'adminUser' => $request->user(),
            'page' => $page->load(['locale', 'author', 'featuredMedia']),
            'preview' => $preview->readiness($page),
            'legal' => $legal->readiness($page),
        ]);
    }
}
