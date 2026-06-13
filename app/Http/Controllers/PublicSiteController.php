<?php

namespace App\Http\Controllers;

use App\Services\PublicSite\PublicHomepageViewDataService;
use App\Services\PublicSite\PublicThemeRenderer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function __invoke(
        Request $request,
        PublicHomepageViewDataService $viewData,
        PublicThemeRenderer $renderer,
    ): View {
        return $renderer->home($viewData->home(
            $request->attributes->get('public_locale'),
            $request->attributes->get('public_theme'),
        ));
    }
}
