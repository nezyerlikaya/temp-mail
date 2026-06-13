<?php

namespace App\Http\Middleware;

use App\Models\Locale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyPublicLocaleDirection
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->attributes->get('public_locale');
        $request->attributes->set(
            'public_direction',
            $locale instanceof Locale && $locale->direction === 'rtl' ? 'rtl' : 'ltr',
        );

        return $next($request);
    }
}
