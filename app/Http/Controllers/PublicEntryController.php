<?php

namespace App\Http\Controllers;

use App\Services\PublicSite\PublicEntryRedirector;
use Illuminate\Http\RedirectResponse;

class PublicEntryController extends Controller
{
    public function __invoke(PublicEntryRedirector $entry): RedirectResponse
    {
        return $entry->response();
    }
}
