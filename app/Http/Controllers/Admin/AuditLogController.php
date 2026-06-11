<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\AuditLogFilterRequest;
use App\Services\Audit\AuditSearchService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __invoke(AuditLogFilterRequest $request, AuditSearchService $search): View
    {
        Gate::authorize('admin.activity-audit-logs.view');

        $filters = $request->validated();

        return view('dashboard.activity-audit-logs.index', [
            'adminUser' => $request->user(),
            'events' => $search->search($filters),
            'filters' => $filters,
            'filterOptions' => $search->options(),
            'summary' => $search->summary($filters),
        ]);
    }
}
