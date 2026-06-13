<?php

namespace App\Http\Controllers;

use App\Services\PublicSite\PublicSitemapService;
use Illuminate\Http\Response;

class PublicSitemapController extends Controller
{
    public function __invoke(PublicSitemapService $sitemap): Response
    {
        return response($sitemap->xml(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
