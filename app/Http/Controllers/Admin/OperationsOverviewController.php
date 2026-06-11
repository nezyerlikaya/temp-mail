<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationsOverviewController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('dashboard.operations-overview.index', [
            'adminUser' => $request->user(),
        ]);
    }
}
