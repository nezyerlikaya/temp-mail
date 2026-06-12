<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Seo\CreateSeoRecordAction;
use App\Actions\Seo\UpdateSeoRecordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seo\PreviewSeoRecordRequest;
use App\Http\Requests\Seo\SeoFilterRequest;
use App\Http\Requests\Seo\UpdateSeoRecordRequest;
use App\Models\SeoRecord;
use App\Services\Seo\SeoEditorService;
use App\Services\Seo\SeoPreviewService;
use App\Services\Seo\SeoStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoGrowthCenterController extends Controller
{
    public function index(SeoFilterRequest $request, SeoStore $store): View
    {
        $filters = [
            'locale' => (string) $request->query('locale', 'all'),
            'target_type' => (string) $request->query('target_type', 'all'),
            'missing_metadata' => (string) $request->query('missing_metadata', 'all'),
            'robots' => (string) $request->query('robots', 'all'),
            'sitemap' => (string) $request->query('sitemap', 'all'),
        ];

        return view('dashboard.seo-growth-center.index', [
            'adminUser' => $request->user(),
            'records' => $store->search([...$request->validated(), ...$filters]),
            'summary' => $store->summary(),
            'filters' => $filters,
            'locales' => $store->locales(),
            'targetTypes' => $store->targetTypes(),
            'targetQueue' => $store->targetQueue($filters['locale']),
            'canUpdateSeo' => $request->user()?->can('admin.seo-growth-center.update') ?? false,
            'canManageSeoSettings' => $request->user()?->can('admin.seo-growth-center.manage') ?? false,
        ]);
    }

    public function create(Request $request, SeoEditorService $editor): View
    {
        $request->user()?->can('admin.seo-growth-center.update') || abort(403);

        return view('dashboard.seo-growth-center.create', [
            'adminUser' => $request->user(),
            'editor' => $editor->data(null, $request->user()),
        ]);
    }

    public function ensure(Request $request, CreateSeoRecordAction $create): RedirectResponse
    {
        $request->user()?->can('admin.seo-growth-center.update') || abort(403);
        $validated = $request->validate([
            'locale_id' => ['required', 'integer', 'exists:locales,id'],
            'target_type' => ['required', 'string', 'max:64'],
            'target_key' => ['required', 'string', 'max:160'],
        ]);

        $record = $create->handle($request->user(), (int) $validated['locale_id'], (string) $validated['target_type'], (string) $validated['target_key']);

        return redirect()
            ->route('admin.seo-growth-center.records.edit', $record)
            ->with('status', 'SEO record foundation prepared.');
    }

    public function edit(Request $request, SeoRecord $seoRecord, SeoEditorService $editor): View
    {
        $request->user()?->can('admin.seo-growth-center.update') || abort(403);

        return view('dashboard.seo-growth-center.edit', [
            'adminUser' => $request->user(),
            'record' => $seoRecord->load(['locale', 'ogImage', 'twitterImage']),
            'editor' => $editor->data($seoRecord, $request->user()),
        ]);
    }

    public function update(UpdateSeoRecordRequest $request, SeoRecord $seoRecord, UpdateSeoRecordAction $update): RedirectResponse
    {
        $update->handle($request->user(), $seoRecord, $request->validated());

        return redirect()
            ->route('admin.seo-growth-center.records.edit', $seoRecord)
            ->with('status', 'SEO record updated.');
    }

    public function preview(PreviewSeoRecordRequest $request, SeoRecord $seoRecord, SeoPreviewService $preview): JsonResponse
    {
        $record = $seoRecord->replicate();
        $record->fill($request->validated());
        $record->setRelation('ogImage', $seoRecord->ogImage);
        $record->setRelation('twitterImage', $seoRecord->twitterImage);

        return response()->json([
            'preview' => $preview->preview($record),
        ]);
    }
}
