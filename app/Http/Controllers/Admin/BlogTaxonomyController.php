<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Blog\CreateCategoryAction;
use App\Actions\Blog\CreateTagAction;
use App\Actions\Blog\UpdateCategoryAction;
use App\Actions\Blog\UpdateTagAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\BlogTaxonomyFilterRequest;
use App\Http\Requests\Blog\StoreCategoryRequest;
use App\Http\Requests\Blog\StoreTagRequest;
use App\Http\Requests\Blog\UpdateCategoryRequest;
use App\Http\Requests\Blog\UpdateTagRequest;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Services\Blog\BlogTaxonomyService;
use App\Services\Blog\CategorySearchService;
use App\Services\Blog\TagSearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlogTaxonomyController extends Controller
{
    public function index(
        BlogTaxonomyFilterRequest $request,
        BlogTaxonomyService $taxonomy,
        CategorySearchService $categories,
        TagSearchService $tags,
    ): View {
        $filters = [
            'tab' => (string) $request->query('tab', 'categories'),
            'q' => (string) $request->query('q', ''),
            'locale_id' => (string) $request->query('locale_id', 'all'),
            'status' => (string) $request->query('status', 'all'),
        ];

        $editingCategory = filled($request->query('edit_category'))
            ? BlogCategory::query()->with('locale')->find($request->integer('edit_category'))
            : null;

        $editingTag = filled($request->query('edit_tag'))
            ? BlogTag::query()->with('locale')->find($request->integer('edit_tag'))
            : null;

        return view('dashboard.taxonomy.index', [
            'adminUser' => $request->user(),
            'summary' => $taxonomy->summary(),
            'filters' => $filters,
            'locales' => $taxonomy->locales(),
            'statuses' => $taxonomy->statuses(),
            'categories' => $categories->search([...$request->validated(), ...$filters]),
            'tags' => $tags->search([...$request->validated(), ...$filters]),
            'editingCategory' => $editingCategory,
            'editingTag' => $editingTag,
            'canCreateTaxonomy' => $request->user()?->can('admin.taxonomy.create') ?? false,
            'canUpdateTaxonomy' => $request->user()?->can('admin.taxonomy.update') ?? false,
        ]);
    }

    public function storeCategory(StoreCategoryRequest $request, CreateCategoryAction $create): RedirectResponse
    {
        $category = $create->handle($request->user(), $request->validated());

        return redirect()
            ->route('admin.taxonomy.index', ['tab' => 'categories', 'edit_category' => $category->id])
            ->with('status', 'Category created.');
    }

    public function updateCategory(UpdateCategoryRequest $request, BlogCategory $blogCategory, UpdateCategoryAction $update): RedirectResponse
    {
        $update->handle($request->user(), $blogCategory, $request->validated());

        return redirect()
            ->route('admin.taxonomy.index', ['tab' => 'categories', 'edit_category' => $blogCategory->id])
            ->with('status', 'Category updated.');
    }

    public function storeTag(StoreTagRequest $request, CreateTagAction $create): RedirectResponse
    {
        $tag = $create->handle($request->user(), $request->validated());

        return redirect()
            ->route('admin.taxonomy.index', ['tab' => 'tags', 'edit_tag' => $tag->id])
            ->with('status', 'Tag created.');
    }

    public function updateTag(UpdateTagRequest $request, BlogTag $blogTag, UpdateTagAction $update): RedirectResponse
    {
        $update->handle($request->user(), $blogTag, $request->validated());

        return redirect()
            ->route('admin.taxonomy.index', ['tab' => 'tags', 'edit_tag' => $blogTag->id])
            ->with('status', 'Tag updated.');
    }
}
