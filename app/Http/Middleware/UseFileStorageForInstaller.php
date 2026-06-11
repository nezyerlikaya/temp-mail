<?php

namespace App\Http\Middleware;

use App\Services\Installer\InstallState;
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
        if (app(InstallState::class)->isRecoveringEnvironment() && ! $request->is('install*')) {
            return redirect()->route('install.readiness');
        }

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
