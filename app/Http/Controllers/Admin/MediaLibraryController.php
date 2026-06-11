<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Media\AttachMediaUsageAction;
use App\Actions\Media\DetachMediaUsageAction;
use App\Actions\Media\MediaUploadAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Media\AttachMediaUsageRequest;
use App\Http\Requests\Media\DetachMediaUsageRequest;
use App\Http\Requests\Media\MediaFilterRequest;
use App\Http\Requests\Media\MediaPickerSearchRequest;
use App\Http\Requests\Media\UpdateMediaRequest;
use App\Http\Requests\Media\UploadMediaRequest;
use App\Models\MediaAsset;
use App\Services\Media\MediaAssetService;
use App\Services\Media\MediaPickerSearchService;
use App\Services\Media\MediaSearchService;
use App\Services\Media\MediaUrlResolver;
use App\Services\Media\MediaUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaLibraryController extends Controller
{
    public function index(
        MediaFilterRequest $request,
        MediaAssetService $assets,
        MediaSearchService $search,
        MediaPickerSearchService $picker,
        MediaUrlResolver $urls,
    ): View {
        $request->user()?->can('admin.media-library.view') || abort(403);

        $recent = $assets->recent();

        $media = $search->search($request->validated());

        return view('dashboard.media-library.index', [
            'adminUser' => $request->user(),
            'summary' => $assets->summary(),
            'recent' => $recent,
            'media' => $media,
            'filters' => [
                'q' => (string) $request->query('q', ''),
                'type' => (string) $request->query('type', 'all'),
                'status' => (string) $request->query('status', 'all'),
                'uploader' => (string) $request->query('uploader', ''),
                'date' => (string) $request->query('date', 'all'),
            ],
            'uploadTargets' => [
                'image' => 'Image',
                'document' => 'Document',
                'avatar' => 'Avatar',
                'seo' => 'SEO / OG',
            ],
            'urls' => $media->getCollection()->mapWithKeys(fn (MediaAsset $asset): array => [
                $asset->id => $urls->url($asset),
            ]),
            'recentUrls' => $recent->mapWithKeys(fn (MediaAsset $asset): array => [
                $asset->id => $urls->url($asset),
            ]),
            'pickerAssets' => $picker->options(['type' => 'all']),
            'canUploadMedia' => $request->user()?->can('admin.media-library.upload') ?? false,
            'canUpdateMedia' => $request->user()?->can('admin.media-library.update') ?? false,
        ]);
    }

    public function store(UploadMediaRequest $request, MediaUploadAction $upload): RedirectResponse
    {
        $upload->handle($request->user(), $request->file('file'), [
            'title' => $request->validated('title'),
            'alt_text' => $request->validated('alt_text'),
            'caption' => $request->validated('caption'),
            'type' => $request->validated('type') ?: null,
            'status' => $request->validated('status') ?: 'active',
        ]);

        return redirect()
            ->route('admin.media-library.index')
            ->with('status', 'Media asset uploaded successfully.');
    }

    public function edit(Request $request, MediaAsset $mediaAsset, MediaUrlResolver $urls, MediaUsageService $usage): View
    {
        $request->user()?->can('admin.media-library.view') || abort(403);

        return view('dashboard.media-library.edit', [
            'adminUser' => $request->user(),
            'asset' => $mediaAsset->load('uploader'),
            'url' => $urls->url($mediaAsset),
            'usages' => $usage->forAsset($mediaAsset),
            'usageSummary' => $usage->summary($mediaAsset),
            'canUpdateMedia' => $request->user()?->can('admin.media-library.update') ?? false,
        ]);
    }

    public function update(UpdateMediaRequest $request, MediaAsset $mediaAsset): RedirectResponse
    {
        $mediaAsset->update($request->validated());

        return redirect()
            ->route('admin.media-library.edit', $mediaAsset)
            ->with('status', 'Media asset updated.');
    }

    public function picker(MediaPickerSearchRequest $request, MediaPickerSearchService $search): JsonResponse
    {
        $assets = $search->search($request->validated());

        return response()->json([
            'assets' => $assets->map(fn (MediaAsset $asset): array => [
                ...$search->option($asset),
                'status' => $asset->status,
            ])->values(),
        ]);
    }

    public function attachUsage(AttachMediaUsageRequest $request, AttachMediaUsageAction $attach): RedirectResponse
    {
        $asset = MediaAsset::query()->findOrFail($request->integer('media_asset_id'));
        $attach->handle($request->user(), $asset, $request->validated());

        return back()->with('status', 'Media usage attached.');
    }

    public function detachUsage(DetachMediaUsageRequest $request, DetachMediaUsageAction $detach): RedirectResponse
    {
        $detach->handle($request->user(), $request->validated());

        return back()->with('status', 'Media usage detached.');
    }
}
