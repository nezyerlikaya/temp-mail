<?php

namespace App\Http\Middleware;

use App\Services\Themes\ThemeResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyActivePublicTheme
{
    public function __construct(private readonly ThemeResolver $themes) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('public_theme', $this->themes->active());

        return $next($request);
    }
}
