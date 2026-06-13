<?php

namespace App\Http\Controllers;

use App\Services\PublicSite\PublicPageService;
use App\Services\PublicSite\PublicThemeRenderer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function show(Request $request, string $locale, string $slug, PublicPageService $pages, PublicThemeRenderer $renderer): View
    {
        return $renderer->page($pages->show($request, $request->attributes->get('public_locale'), $request->attributes->get('public_theme'), $slug));
    }
}
