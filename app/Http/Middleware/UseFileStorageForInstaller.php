<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseFileStorageForInstaller
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('install*')) {
            config([
                'cache.default' => 'file',
                'queue.default' => 'sync',
                'session.driver' => 'file',
            ]);
        }

        return $next($request);
    }
}
