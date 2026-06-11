<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Pages\CreatePageAction;
use App\Actions\Pages\UpdatePageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pages\PageFilterRequest;
use App\Http\Requests\Pages\StorePageRequest;
use App\Http\Requests\Pages\UpdatePageRequest;
use App\Models\Page;
use App\Services\Pages\PageSearchService;
use App\Services\Pages\PageStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageStudioController extends Controller
{
    public function index(PageFilterRequest $request, PageStore $store, PageSearchService $search): View
    {
        return view('dashboard.page-studio.index', [
            'adminUser' => $request->user(),
            'pages' => $search->search($request->validated()),
            'summary' => $store->summary(),
            'recent' => $store->recent(),
            'filters' => [
                'q' => (string) $request->query('q', ''),
                'locale_id' => (string) $request->query('locale_id', 'all'),
                'page_type' => (string) $request->query('page_type', 'all'),
                'status' => (string) $request->query('status', 'all'),
                'author_id' => (string) $request->query('author_id', 'all'),
                'date' => (string) $request->query('date', 'all'),
            ],
            'locales' => $store->locales(),
            'authors' => $store->authors(),
            'pageTypes' => $store->pageTypes(),
            'statuses' => $store->statuses(),
            'canCreatePage' => $request->user()?->can('admin.page-studio.create') ?? false,
        ]);
    }

    public function create(PageFilterRequest $request, PageStore $store): View
    {
        $request->user()?->can('admin.page-studio.create') || abort(403);

        return view('dashboard.page-studio.create', [
            'adminUser' => $request->user(),
            'page' => null,
            'locales' => $store->locales(),
            'pageTypes' => $store->pageTypes(),
            'statuses' => $store->statuses(),
            'readinessOptions' => $store->contentReadinessOptions(),
        ]);
    }

    public function store(StorePageRequest $request, CreatePageAction $create): RedirectResponse
    {
        $page = $create->handle($request->user(), $request->validated());

        return redirect()
            ->route('admin.page-studio.edit', $page)
            ->with('status', 'Page foundation created.');
    }

    public function edit(PageFilterRequest $request, Page $page, PageStore $store): View
    {
        $request->user()?->can('admin.page-studio.update') || abort(403);

        return view('dashboard.page-studio.edit', [
            'adminUser' => $request->user(),
            'page' => $page->load(['locale', 'author']),
            'locales' => $store->locales(),
            'pageTypes' => $store->pageTypes(),
            'statuses' => $store->statuses(),
            'readinessOptions' => $store->contentReadinessOptions(),
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page, UpdatePageAction $update): RedirectResponse
    {
        $update->handle($request->user(), $page, $request->validated());

        return redirect()
            ->route('admin.page-studio.edit', $page)
            ->with('status', 'Page foundation updated.');
    }
}
