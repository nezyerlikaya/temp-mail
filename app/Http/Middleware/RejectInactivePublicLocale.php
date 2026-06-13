<?php

namespace App\Http\Middleware;

use App\Models\Locale;
use App\Services\PublicSite\PublicLocaleResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectInactivePublicLocale
{
    public function __construct(private readonly PublicLocaleResolver $locales) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->attributes->get('public_locale');

        abort_unless($locale instanceof Locale && $this->locales->isPublic($locale), 404);

        return $next($request);
    }
}
