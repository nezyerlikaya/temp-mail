<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\AuditLogFilterRequest;
use App\Http\Requests\Audit\ExportAuditLogsRequest;
use App\Http\Requests\Audit\UpdateAuditRetentionRequest;
use App\Services\Audit\AuditDiffService;
use App\Services\Audit\AuditExportService;
use App\Services\Audit\AuditRetentionService;
use App\Services\Audit\AuditSearchService;
use App\Services\Audit\AuditTamperCheckService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(
        AuditLogFilterRequest $request,
        AuditSearchService $search,
        AuditDiffService $diff,
        AuditRetentionService $retention,
        AuditTamperCheckService $tamper,
    ): View {
        Gate::authorize('admin.activity-audit-logs.view');

        $filters = $request->validated();
        $events = $search->search($filters);

        return view('dashboard.activity-audit-logs.index', [
            'adminUser' => $request->user(),
            'events' => $events,
            'diffs' => $diff->forEvents($events->getCollection()),
            'filters' => $filters,
            'filterOptions' => $search->options(),
            'summary' => $search->summary($filters),
            'retention' => $retention->status(),
            'tamperReadiness' => $tamper->readiness(),
            'canExport' => $request->user()?->can('admin.activity-audit-logs.export') === true,
            'canManageRetention' => $request->user()?->can('admin.activity-audit-logs.manage-retention') === true,
        ]);
    }

    public function export(ExportAuditLogsRequest $request, AuditExportService $export): StreamedResponse
    {
        Gate::authorize('admin.activity-audit-logs.export');

        return $export->stream($request->validated(), $request->user());
    }

    public function updateRetention(UpdateAuditRetentionRequest $request, AuditRetentionService $retention): RedirectResponse
    {
        Gate::authorize('admin.activity-audit-logs.manage-retention');

        $retention->update($request->user(), $request->validated());

        return redirect()->route('admin.activity-audit-logs.index')->with('status', 'Audit retention settings saved.');
    }
}
