<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Blog\CreateBlogPostAction;
use App\Actions\Blog\UpdateBlogPostAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\BlogPostFilterRequest;
use App\Http\Requests\Blog\StoreBlogPostRequest;
use App\Http\Requests\Blog\UpdateBlogPostRequest;
use App\Models\BlogPost;
use App\Services\Blog\BlogPostEditorService;
use App\Services\Blog\BlogPostSearchService;
use App\Services\Blog\BlogPostStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlogStudioController extends Controller
{
    public function index(BlogPostFilterRequest $request, BlogPostStore $store, BlogPostSearchService $search): View
    {
        $filters = [
            'q' => (string) $request->query('q', ''),
            'locale_id' => (string) $request->query('locale_id', 'all'),
            'status' => (string) $request->query('status', 'all'),
            'category_id' => (string) $request->query('category_id', 'all'),
            'author_id' => (string) $request->query('author_id', 'all'),
            'date' => (string) $request->query('date', 'all'),
        ];

        return view('dashboard.blog-studio.index', [
            'adminUser' => $request->user(),
            'posts' => $search->search([...$request->validated(), ...$filters]),
            'summary' => $store->summary(),
            'recent' => $store->recent(),
            'filters' => $filters,
            'locales' => $store->locales(),
            'categories' => $store->categories(),
            'authors' => $store->authors(),
            'statuses' => $store->statuses(),
            'canCreatePost' => $request->user()?->can('admin.blog-studio.create') ?? false,
        ]);
    }

    public function create(BlogPostFilterRequest $request, BlogPostEditorService $editor): View
    {
        $request->user()?->can('admin.blog-studio.create') || abort(403);

        return view('dashboard.blog-studio.create', [
            'adminUser' => $request->user(),
            'post' => null,
            'editor' => $editor->data(null, $request->user()),
        ]);
    }

    public function store(StoreBlogPostRequest $request, CreateBlogPostAction $create): RedirectResponse
    {
        $post = $create->handle($request->user(), $request->validated());

        return redirect()
            ->route('admin.blog-studio.edit', $post)
            ->with('status', 'Blog post created.');
    }

    public function edit(BlogPostFilterRequest $request, BlogPost $blogPost, BlogPostEditorService $editor): View
    {
        $request->user()?->can('admin.blog-studio.update') || abort(403);
        $blogPost->load(['locale', 'author', 'category', 'tags', 'featuredMedia']);

        return view('dashboard.blog-studio.edit', [
            'adminUser' => $request->user(),
            'post' => $blogPost,
            'editor' => $editor->data($blogPost, $request->user()),
        ]);
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost, UpdateBlogPostAction $update): RedirectResponse
    {
        $update->handle($request->user(), $blogPost, $request->validated());

        return redirect()
            ->route('admin.blog-studio.edit', $blogPost)
            ->with('status', 'Blog post updated.');
    }
}
