<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardSummaryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationsOverviewController extends Controller
{
    public function __invoke(Request $request, DashboardSummaryService $dashboard): View
    {
        return view('dashboard.operations-overview.index', [
            'adminUser' => $request->user(),
            'summary' => $dashboard->summary($request->user()),
        ]);
    }
}
