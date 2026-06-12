<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Blog\CreateBlogPostAction;
use App\Actions\Blog\DeleteBlogPostAction;
use App\Actions\Blog\HideBlogPostAction;
use App\Actions\Blog\PublishBlogPostAction;
use App\Actions\Blog\RestoreBlogPostAction;
use App\Actions\Blog\TrashBlogPostAction;
use App\Actions\Blog\UpdateBlogPostAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\BlogPostFilterRequest;
use App\Http\Requests\Blog\DeleteBlogPostRequest;
use App\Http\Requests\Blog\HideBlogPostRequest;
use App\Http\Requests\Blog\PublishBlogPostRequest;
use App\Http\Requests\Blog\RestoreBlogPostRequest;
use App\Http\Requests\Blog\StoreBlogPostRequest;
use App\Http\Requests\Blog\TrashBlogPostRequest;
use App\Http\Requests\Blog\UpdateBlogPostRequest;
use App\Models\BlogPost;
use App\Services\Blog\BlogPostEditorService;
use App\Services\Blog\BlogPostOwnershipService;
use App\Services\Blog\BlogPostPreviewService;
use App\Services\Blog\BlogPostSearchService;
use App\Services\Blog\BlogPostStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlogStudioController extends Controller
{
    public function index(BlogPostFilterRequest $request, BlogPostStore $store, BlogPostSearchService $search, BlogPostPreviewService $preview): View
    {
        $filters = [
            'q' => (string) $request->query('q', ''),
            'locale_id' => (string) $request->query('locale_id', 'all'),
            'status' => (string) $request->query('status', 'all'),
            'category_id' => (string) $request->query('category_id', 'all'),
            'author_id' => (string) $request->query('author_id', 'all'),
            'date' => (string) $request->query('date', 'all'),
        ];

        $posts = $search->search([...$request->validated(), ...$filters]);

        return view('dashboard.blog-studio.index', [
            'adminUser' => $request->user(),
            'posts' => $posts,
            'summary' => $store->summary(),
            'recent' => $store->recent(),
            'filters' => $filters,
            'locales' => $store->locales(),
            'categories' => $store->categories(),
            'authors' => $store->authors(),
            'statuses' => $store->statuses(),
            'previewUrls' => $search->previewUrls($posts, $preview),
            'canCreatePost' => $request->user()?->can('admin.blog-studio.create') ?? false,
            'canPreviewPost' => $request->user()?->can('admin.blog-studio.preview') ?? false,
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

    public function edit(BlogPostFilterRequest $request, BlogPost $blogPost, BlogPostEditorService $editor, BlogPostPreviewService $preview, BlogPostOwnershipService $ownership): View
    {
        $request->user()?->can('admin.blog-studio.update') || abort(403);
        $blogPost->load(['locale', 'author', 'category', 'tags', 'featuredMedia']);

        return view('dashboard.blog-studio.edit', [
            'adminUser' => $request->user(),
            'post' => $blogPost,
            'editor' => $editor->data($blogPost, $request->user()),
            'preview' => $preview->readiness($blogPost),
            'ownership' => $ownership->readiness($blogPost),
        ]);
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost, UpdateBlogPostAction $update): RedirectResponse
    {
        $update->handle($request->user(), $blogPost, $request->validated());

        return redirect()
            ->route('admin.blog-studio.edit', $blogPost)
            ->with('status', 'Blog post updated.');
    }

    public function publish(PublishBlogPostRequest $request, BlogPost $blogPost, PublishBlogPostAction $publish): RedirectResponse
    {
        $publish->handle($request->user(), $blogPost);

        return redirect()->route('admin.blog-studio.edit', $blogPost)->with('status', 'Blog post published.');
    }

    public function hide(HideBlogPostRequest $request, BlogPost $blogPost, HideBlogPostAction $hide): RedirectResponse
    {
        $hide->handle($request->user(), $blogPost);

        return redirect()->route('admin.blog-studio.edit', $blogPost)->with('status', 'Blog post hidden.');
    }

    public function trash(TrashBlogPostRequest $request, BlogPost $blogPost, TrashBlogPostAction $trash): RedirectResponse
    {
        $trash->handle($request->user(), $blogPost);

        return redirect()->route('admin.blog-studio.index', ['status' => 'trashed'])->with('status', 'Blog post moved to trash.');
    }

    public function restore(RestoreBlogPostRequest $request, BlogPost $blogPost, RestoreBlogPostAction $restore): RedirectResponse
    {
        $restore->handle($request->user(), $blogPost);

        return redirect()->route('admin.blog-studio.edit', $blogPost)->with('status', 'Blog post restored as draft.');
    }

    public function destroy(DeleteBlogPostRequest $request, BlogPost $blogPost, DeleteBlogPostAction $delete): RedirectResponse
    {
        $delete->handle($request->user(), $blogPost);

        return redirect()->route('admin.blog-studio.index', ['status' => 'trashed'])->with('status', 'Permanent delete readiness completed.');
    }

    public function preview(BlogPostFilterRequest $request, BlogPost $blogPost, BlogPostPreviewService $preview): View
    {
        $request->user()?->can('admin.blog-studio.preview') || abort(403);

        return view('dashboard.blog-studio.preview', [
            'adminUser' => $request->user(),
            'post' => $blogPost->load(['locale', 'author', 'category', 'tags', 'featuredMedia']),
            'preview' => $preview->readiness($blogPost),
        ]);
    }
}
