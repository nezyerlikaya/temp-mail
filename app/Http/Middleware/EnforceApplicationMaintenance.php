<?php

namespace App\Http\Middleware;

use App\Services\Installer\InstallState;
use App\Services\Settings\MaintenanceModeService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnforceApplicationMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (app(InstallState::class)->isInstalled()
                && Schema::hasTable('system_settings')
                && app(MaintenanceModeService::class)->shouldBlock($request)) {
                return response()->view('maintenance', [
                    'message' => app(MaintenanceModeService::class)->message(),
                ], 503);
            }
        } catch (Throwable) {
            // Database recovery remains responsible for unavailable settings storage.
        }

        return $next($request);
    }
}
