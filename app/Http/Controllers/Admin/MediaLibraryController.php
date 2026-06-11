<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Media\AttachMediaUsageAction;
use App\Actions\Media\DeleteMediaAction;
use App\Actions\Media\DetachMediaUsageAction;
use App\Actions\Media\MediaUploadAction;
use App\Actions\Media\RestoreMediaAction;
use App\Actions\Media\TrashMediaAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Media\AttachMediaUsageRequest;
use App\Http\Requests\Media\DeleteMediaRequest;
use App\Http\Requests\Media\DetachMediaUsageRequest;
use App\Http\Requests\Media\MediaFilterRequest;
use App\Http\Requests\Media\MediaPickerSearchRequest;
use App\Http\Requests\Media\RestoreMediaRequest;
use App\Http\Requests\Media\TrashMediaRequest;
use App\Http\Requests\Media\UpdateMediaRequest;
use App\Http\Requests\Media\UpdateMediaStatusRequest;
use App\Http\Requests\Media\UploadMediaRequest;
use App\Models\MediaAsset;
use App\Services\Audit\AuditLogger;
use App\Services\Media\AvatarMediaResolver;
use App\Services\Media\MediaAssetService;
use App\Services\Media\MediaLifecycleService;
use App\Services\Media\MediaPickerSearchService;
use App\Services\Media\MediaQualityService;
use App\Services\Media\MediaSearchService;
use App\Services\Media\MediaUrlResolver;
use App\Services\Media\MediaUsageService;
use App\Services\Media\SeoImageReadinessService;
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
                'usage' => (string) $request->query('usage', 'all'),
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
            'canTrashMedia' => $request->user()?->can('admin.media-library.trash') ?? false,
            'canRestoreMedia' => $request->user()?->can('admin.media-library.restore') ?? false,
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

    public function edit(
        Request $request,
        MediaAsset $mediaAsset,
        MediaUrlResolver $urls,
        MediaUsageService $usage,
        MediaLifecycleService $lifecycle,
        MediaQualityService $quality,
        AvatarMediaResolver $avatar,
        SeoImageReadinessService $seo,
    ): View {
        $request->user()?->can('admin.media-library.view') || abort(403);

        return view('dashboard.media-library.edit', [
            'adminUser' => $request->user(),
            'asset' => $mediaAsset->load('uploader'),
            'url' => $urls->url($mediaAsset),
            'usages' => $usage->forAsset($mediaAsset),
            'usageSummary' => $usage->summary($mediaAsset),
            'lifecycle' => $lifecycle->state($mediaAsset),
            'qualityWarnings' => $quality->warnings($mediaAsset),
            'imageMetadata' => $quality->metadata($mediaAsset),
            'avatarReadiness' => $avatar->resolve($mediaAsset),
            'seoReadiness' => $seo->assess($mediaAsset),
            'canUpdateMedia' => $request->user()?->can('admin.media-library.update') ?? false,
            'canTrashMedia' => $request->user()?->can('admin.media-library.trash') ?? false,
            'canRestoreMedia' => $request->user()?->can('admin.media-library.restore') ?? false,
            'canDeleteMedia' => $request->user()?->can('admin.media-library.delete') ?? false,
        ]);
    }

    public function update(UpdateMediaRequest $request, MediaAsset $mediaAsset, AuditLogger $audit): RedirectResponse
    {
        $before = $mediaAsset->only(['title', 'alt_text', 'caption']);
        $mediaAsset->update($request->validated());
        $changes = collect($mediaAsset->only(['title', 'alt_text', 'caption']))
            ->filter(fn (mixed $value, string $key): bool => $before[$key] !== $value)
            ->all();

        if ($changes !== []) {
            $audit->record('media.updated', $request->user(), null, [
                'media_uuid' => $mediaAsset->uuid,
                'changes' => $changes,
            ], ['module' => 'media', 'action' => 'Update media metadata', 'target' => $mediaAsset]);
        }

        return redirect()
            ->route('admin.media-library.edit', $mediaAsset)
            ->with('status', 'Media asset updated.');
    }

    public function updateStatus(
        UpdateMediaStatusRequest $request,
        MediaAsset $mediaAsset,
        MediaLifecycleService $lifecycle,
        AuditLogger $audit,
    ): RedirectResponse {
        $previousStatus = $mediaAsset->status;
        $lifecycle->updateStatus($mediaAsset, $request->string('status')->toString());

        $audit->record('media.status_updated', $request->user(), null, [
            'media_uuid' => $mediaAsset->uuid,
            'previous_status' => $previousStatus,
            'status' => $mediaAsset->status,
        ], ['module' => 'media', 'action' => 'Update media status', 'target' => $mediaAsset]);

        return back()->with('status', 'Media visibility updated.');
    }

    public function trash(TrashMediaRequest $request, MediaAsset $mediaAsset, TrashMediaAction $trash): RedirectResponse
    {
        $trash->handle($request->user(), $mediaAsset);

        return back()->with('status', 'Media asset moved to trash.');
    }

    public function restore(RestoreMediaRequest $request, MediaAsset $mediaAsset, RestoreMediaAction $restore): RedirectResponse
    {
        $restore->handle($request->user(), $mediaAsset);

        return back()->with('status', 'Media asset restored.');
    }

    public function destroy(DeleteMediaRequest $request, MediaAsset $mediaAsset, DeleteMediaAction $delete): RedirectResponse
    {
        $delete->handle(
            $request->user(),
            $mediaAsset,
            $request->boolean('confirm_in_use_delete'),
        );

        return redirect()
            ->route('admin.media-library.index', ['status' => 'trashed'])
            ->with('status', 'Media asset permanently deleted.');
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
