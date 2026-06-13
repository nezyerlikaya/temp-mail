<?php

namespace App\Http\Middleware;

use App\Services\PublicSite\PublicLocaleResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolvePublicLocale
{
    public function __construct(private readonly PublicLocaleResolver $locales) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->locales->find((string) $request->route('locale'));

        abort_unless($locale, 404);

        app()->setLocale($locale->locale);
        $request->attributes->set('public_locale', $locale);

        return $next($request);
    }
}
