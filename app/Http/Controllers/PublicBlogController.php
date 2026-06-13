<?php

namespace App\Http\Controllers;

use App\Services\PublicSite\PublicBlogIndexService;
use App\Services\PublicSite\PublicBlogPostService;
use App\Services\PublicSite\PublicTaxonomyService;
use App\Services\PublicSite\PublicThemeRenderer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicBlogController extends Controller
{
    public function index(Request $request, string $locale, PublicBlogIndexService $blog, PublicThemeRenderer $renderer): View
    {
        return $renderer->blogIndex($blog->index($request, $request->attributes->get('public_locale'), $request->attributes->get('public_theme')));
    }

    public function show(Request $request, string $locale, string $slug, PublicBlogPostService $post, PublicThemeRenderer $renderer): View
    {
        return $renderer->blogShow($post->show($request, $request->attributes->get('public_locale'), $request->attributes->get('public_theme'), $slug));
    }

    public function category(Request $request, string $locale, string $slug, PublicTaxonomyService $taxonomy, PublicThemeRenderer $renderer): View
    {
        return $renderer->blogCategory($taxonomy->category($request, $request->attributes->get('public_locale'), $request->attributes->get('public_theme'), $slug));
    }

    public function tag(Request $request, string $locale, string $slug, PublicTaxonomyService $taxonomy, PublicThemeRenderer $renderer): View
    {
        return $renderer->blogTag($taxonomy->tag($request, $request->attributes->get('public_locale'), $request->attributes->get('public_theme'), $slug));
    }

    public function author(Request $request, string $locale, string $slug, PublicTaxonomyService $taxonomy, PublicThemeRenderer $renderer): View
    {
        return $renderer->blogAuthor($taxonomy->author($request, $request->attributes->get('public_locale'), $request->attributes->get('public_theme'), $slug));
    }
}
