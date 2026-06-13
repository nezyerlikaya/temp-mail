<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Abuse\AddAbuseCaseNoteAction;
use App\Actions\Abuse\AddAbuseEvidenceAction;
use App\Actions\Abuse\ArchiveAbuseCaseAction;
use App\Actions\Abuse\AssignAbuseCaseAction;
use App\Actions\Abuse\RejectAbuseCaseAction;
use App\Actions\Abuse\RemoveAbuseEvidenceAction;
use App\Actions\Abuse\ReopenAbuseCaseAction;
use App\Actions\Abuse\ResolveAbuseCaseAction;
use App\Actions\Abuse\UpdateAbuseCaseStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Abuse\AbuseReportFilterRequest;
use App\Http\Requests\Abuse\AddAbuseCaseNoteRequest;
use App\Http\Requests\Abuse\AddAbuseEvidenceRequest;
use App\Http\Requests\Abuse\ArchiveAbuseCaseRequest;
use App\Http\Requests\Abuse\AssignAbuseCaseRequest;
use App\Http\Requests\Abuse\ExecuteAbuseOperationalActionRequest;
use App\Http\Requests\Abuse\RejectAbuseCaseRequest;
use App\Http\Requests\Abuse\RemoveAbuseEvidenceRequest;
use App\Http\Requests\Abuse\ReopenAbuseCaseRequest;
use App\Http\Requests\Abuse\ResolveAbuseCaseRequest;
use App\Http\Requests\Abuse\UpdateAbuseCaseStatusRequest;
use App\Models\AbuseEvidence;
use App\Models\AbuseReport;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Abuse\AbuseCaseService;
use App\Services\Abuse\AbuseCaseTimelineService;
use App\Services\Abuse\AbuseOperationalActionDispatcher;
use App\Services\Abuse\AbuseReporterNotificationService;
use App\Services\Abuse\AbuseReportSearchService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function show(
        AbuseReportFilterRequest $request,
        AbuseReport $abuseReport,
        AbuseCaseTimelineService $timeline,
        AbuseReporterNotificationService $reporterNotification,
    ): View {
        $abuseReport->load(['assignee', 'domain', 'mailbox', 'reportedUser', 'relatedComment', 'resolver', 'notes.author', 'evidences.mediaAsset', 'evidences.addedBy']);

        return view('dashboard.abuse-reports.show', [
            'report' => $abuseReport,
            'timeline' => $timeline->forCase($abuseReport),
            'mediaAssets' => $request->user()?->can('manage abuse evidence')
                ? MediaAsset::query()->whereIn('status', ['active', 'hidden'])->latest()->limit(100)->get(['id', 'uuid', 'title', 'original_name', 'mime_type', 'size_bytes'])
                : collect(),
            'reporterReadiness' => $reporterNotification->readiness($abuseReport),
            'administrators' => $this->administrators(),
            'canAssign' => $request->user()?->can('assign abuse case') ?? false,
            'canUpdateStatus' => $request->user()?->can('update abuse case status') ?? false,
            'canViewSensitive' => $request->user()?->can('view sensitive abuse reporter information') ?? false,
            'canViewEvidence' => $request->user()?->can('view abuse case evidence') ?? false,
            'canManageEvidence' => $request->user()?->can('manage abuse evidence') ?? false,
            'canAddNotes' => $request->user()?->can('add internal abuse notes') ?? false,
            'canResolve' => $request->user()?->can('resolve or reject abuse case') ?? false,
            'canReopenArchive' => $request->user()?->can('reopen or archive abuse case') ?? false,
            'canExecuteActions' => $request->user()?->can('execute abuse operational actions') ?? false,
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

    public function note(AddAbuseCaseNoteRequest $request, AbuseReport $abuseReport, AddAbuseCaseNoteAction $action): RedirectResponse
    {
        $action->handle($request->user(), $abuseReport, $request->validated('body'));

        return back()->with('status', 'Internal note added.');
    }

    public function addEvidence(AddAbuseEvidenceRequest $request, AbuseReport $abuseReport, AddAbuseEvidenceAction $action): RedirectResponse
    {
        $asset = MediaAsset::query()->findOrFail($request->integer('media_asset_id'));
        $action->handle($request->user(), $abuseReport, $asset, $request->validated('label'));

        return back()->with('status', 'Protected evidence added.');
    }

    public function removeEvidence(RemoveAbuseEvidenceRequest $request, AbuseReport $abuseReport, AbuseEvidence $abuseEvidence, RemoveAbuseEvidenceAction $action): RedirectResponse
    {
        $action->handle($request->user(), $abuseReport, $abuseEvidence);

        return back()->with('status', 'Evidence reference removed.');
    }

    public function downloadEvidence(Request $request, AbuseReport $abuseReport, AbuseEvidence $abuseEvidence): StreamedResponse
    {
        if (! $request->user()?->can('view abuse case evidence')) {
            throw new AuthorizationException;
        }

        abort_unless($abuseEvidence->abuse_report_id === $abuseReport->id, 404);
        abort_unless(Storage::disk($abuseEvidence->private_disk)->exists($abuseEvidence->private_path), 404);

        return Storage::disk($abuseEvidence->private_disk)->download(
            $abuseEvidence->private_path,
            $abuseEvidence->mediaAsset?->original_name ?? 'case-evidence',
            ['Cache-Control' => 'private, no-store'],
        );
    }

    public function resolve(ResolveAbuseCaseRequest $request, AbuseReport $abuseReport, ResolveAbuseCaseAction $action): RedirectResponse
    {
        $action->handle($request->user(), $abuseReport, $request->validated());

        return back()->with('status', 'Case resolved.');
    }

    public function reject(RejectAbuseCaseRequest $request, AbuseReport $abuseReport, RejectAbuseCaseAction $action): RedirectResponse
    {
        $action->handle($request->user(), $abuseReport, $request->validated());

        return back()->with('status', 'Case rejected as invalid.');
    }

    public function reopen(ReopenAbuseCaseRequest $request, AbuseReport $abuseReport, ReopenAbuseCaseAction $action): RedirectResponse
    {
        $action->handle($request->user(), $abuseReport, $request->validated('reason'));

        return back()->with('status', 'Case reopened.');
    }

    public function archive(ArchiveAbuseCaseRequest $request, AbuseReport $abuseReport, ArchiveAbuseCaseAction $action): RedirectResponse
    {
        $action->handle($request->user(), $abuseReport, $request->validated('reason'));

        return back()->with('status', 'Case archived.');
    }

    public function operationalAction(ExecuteAbuseOperationalActionRequest $request, AbuseReport $abuseReport, AbuseOperationalActionDispatcher $dispatcher): RedirectResponse
    {
        $summary = $dispatcher->dispatch($request->user(), $abuseReport, $request->validated());

        return back()->with('status', $summary);
    }

    private function administrators()
    {
        return User::query()->where('status', 'active')->whereIn('role', ['owner', 'admin', 'moderator'])->orderBy('name')->get(['id', 'name', 'role']);
    }
}
