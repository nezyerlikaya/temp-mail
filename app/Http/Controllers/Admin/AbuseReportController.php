<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Abuse\AssignAbuseCaseAction;
use App\Actions\Abuse\UpdateAbuseCaseStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Abuse\AbuseReportFilterRequest;
use App\Http\Requests\Abuse\AssignAbuseCaseRequest;
use App\Http\Requests\Abuse\UpdateAbuseCaseStatusRequest;
use App\Models\AbuseReport;
use App\Models\User;
use App\Services\Abuse\AbuseCaseService;
use App\Services\Abuse\AbuseReportSearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AbuseReportController extends Controller
{
    public function index(AbuseReportFilterRequest $request, AbuseReportSearchService $search, AbuseCaseService $cases): View
    {
        $filters = $request->filters();

        return view('dashboard.abuse-reports.index', [
            'reports' => $search->search($filters),
            'filters' => $filters,
            'summary' => $cases->summary(),
            'administrators' => $this->administrators(),
            'canViewSensitive' => $request->user()?->can('view sensitive abuse reporter information') ?? false,
        ]);
    }

    public function show(AbuseReportFilterRequest $request, AbuseReport $abuseReport): View
    {
        $abuseReport->load(['assignee', 'domain', 'mailbox', 'reportedUser']);

        return view('dashboard.abuse-reports.show', [
            'report' => $abuseReport,
            'administrators' => $this->administrators(),
            'canAssign' => $request->user()?->can('assign abuse case') ?? false,
            'canUpdateStatus' => $request->user()?->can('update abuse case status') ?? false,
            'canViewSensitive' => $request->user()?->can('view sensitive abuse reporter information') ?? false,
        ]);
    }

    public function assign(AssignAbuseCaseRequest $request, AbuseReport $abuseReport, AssignAbuseCaseAction $action): RedirectResponse
    {
        $assignee = filled($request->validated('assigned_to')) ? User::query()->findOrFail($request->validated('assigned_to')) : null;
        $action->handle($request->user(), $abuseReport, $assignee);

        return back()->with('status', 'Case assignment updated.');
    }

    public function status(UpdateAbuseCaseStatusRequest $request, AbuseReport $abuseReport, UpdateAbuseCaseStatusAction $action): RedirectResponse
    {
        $action->handle($request->user(), $abuseReport, $request->validated('status'));

        return back()->with('status', 'Case status updated.');
    }

    private function administrators()
    {
        return User::query()->where('status', 'active')->whereIn('role', ['owner', 'admin', 'moderator'])->orderBy('name')->get(['id', 'name', 'role']);
    }
}
