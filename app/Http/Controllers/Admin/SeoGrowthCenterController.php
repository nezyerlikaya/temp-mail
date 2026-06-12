<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Seo\CreateSeoRecordAction;
use App\Actions\Seo\UpdateSeoRecordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seo\PreviewSeoRecordRequest;
use App\Http\Requests\Seo\RollbackSeoVersionRequest;
use App\Http\Requests\Seo\RunSeoDiagnosticsRequest;
use App\Http\Requests\Seo\SeoFilterRequest;
use App\Http\Requests\Seo\StoreRedirectRequest;
use App\Http\Requests\Seo\UpdateRedirectRequest;
use App\Http\Requests\Seo\UpdateSeoRecordRequest;
use App\Http\Requests\Seo\UpdateSeoTemplateRequest;
use App\Models\SeoRecord;
use App\Models\SeoRedirect;
use App\Models\SeoVersion;
use App\Services\Audit\AuditLogger;
use App\Services\Seo\RedirectService;
use App\Services\Seo\SeoDiagnosticsService;
use App\Services\Seo\SeoEditorService;
use App\Services\Seo\SeoPreviewService;
use App\Services\Seo\SeoStore;
use App\Services\Seo\SeoTemplateService;
use App\Services\Seo\SeoTemplateVariableRegistry;
use App\Services\Seo\SeoVersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoGrowthCenterController extends Controller
{
    public function index(
        SeoFilterRequest $request,
        SeoStore $store,
        SeoDiagnosticsService $diagnostics,
        SeoTemplateService $templates,
        SeoTemplateVariableRegistry $variables,
        RedirectService $redirects,
        SeoVersionService $versions,
    ): View {
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
            'diagnostics' => $diagnostics->dashboard($request->only(['severity', 'issue'])),
            'templates' => $templates->templates(),
            'templateVariables' => $variables->variables(),
            'redirects' => $redirects->redirects(),
            'versions' => $versions->latest(),
            'canUpdateSeo' => $request->user()?->can('admin.seo-growth-center.update') ?? false,
            'canManageSeoSettings' => $request->user()?->can('admin.seo-growth-center.manage') ?? false,
            'canRunDiagnostics' => $request->user()?->can('admin.seo-growth-center.diagnostics') ?? false,
            'canManageTemplates' => $request->user()?->can('admin.seo-growth-center.templates') ?? false,
            'canManageRedirects' => $request->user()?->can('admin.seo-growth-center.redirects') ?? false,
            'canManageReadiness' => $request->user()?->can('admin.seo-growth-center.readiness') ?? false,
            'canRollbackSeo' => $request->user()?->can('admin.seo-growth-center.rollback') ?? false,
        ]);
    }

    public function runDiagnostics(RunSeoDiagnosticsRequest $request, AuditLogger $audit): RedirectResponse
    {
        $audit->record('seo.diagnostics_run', $request->user(), null, [
            'severity' => $request->validated('severity', 'all'),
            'issue' => $request->validated('issue', 'all'),
        ], ['module' => 'seo', 'action' => 'Run SEO diagnostics']);

        return redirect()
            ->route('admin.seo-growth-center.index', $request->validated())
            ->with('status', 'SEO diagnostics refreshed.');
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

    public function saveTemplate(UpdateSeoTemplateRequest $request, SeoTemplateService $templates): RedirectResponse
    {
        $templates->save($request->user(), $request->validated());

        return redirect()
            ->route('admin.seo-growth-center.index')
            ->with('status', 'SEO template saved.');
    }

    public function storeRedirect(StoreRedirectRequest $request, RedirectService $redirects): RedirectResponse
    {
        $redirects->store($request->user(), $request->validated());

        return redirect()
            ->route('admin.seo-growth-center.index')
            ->with('status', 'Redirect rule created.');
    }

    public function updateRedirect(UpdateRedirectRequest $request, SeoRedirect $seoRedirect, RedirectService $redirects): RedirectResponse
    {
        $redirects->update($request->user(), $seoRedirect, $request->validated());

        return redirect()
            ->route('admin.seo-growth-center.index')
            ->with('status', 'Redirect rule updated.');
    }

    public function rollback(RollbackSeoVersionRequest $request, SeoVersion $seoVersion, SeoVersionService $versions): RedirectResponse
    {
        $record = $versions->rollback($request->user(), $seoVersion);

        return redirect()
            ->route('admin.seo-growth-center.records.edit', $record)
            ->with('status', 'SEO rollback readiness applied from version history.');
    }
}
