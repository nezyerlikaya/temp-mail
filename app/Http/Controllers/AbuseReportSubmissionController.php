<?php

namespace App\Http\Controllers;

use App\Actions\Abuse\SubmitAbuseReportAction;
use App\Http\Requests\Abuse\SubmitAbuseReportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AbuseReportSubmissionController extends Controller
{
    public function create(): View
    {
        return view('abuse-report.create');
    }

    public function store(SubmitAbuseReportRequest $request, SubmitAbuseReportAction $action): RedirectResponse
    {
        $report = $action->handle($request->validated(), $request);

        return redirect()->route('abuse-report.create')->with('status', 'Report received. Your case reference is '.$report->case_reference.'.');
    }
}
