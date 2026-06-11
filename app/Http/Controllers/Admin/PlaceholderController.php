<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminNavigationRegistry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlaceholderController extends Controller
{
    public function __invoke(Request $request, AdminNavigationRegistry $navigation): View
    {
        $item = $navigation->findByRoute($request->route()?->getName());

        abort_if($item === null, 404);

        return view('dashboard.placeholder.index', [
            'adminUser' => $request->user(),
            'navigationItem' => $item,
        ]);
    }
}
