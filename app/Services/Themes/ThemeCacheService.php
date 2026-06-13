<?php

namespace App\Services\Themes;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Throwable;

class ThemeCacheService
{
    public const ACTIVE_THEME_KEY = 'themes.active';

    public const REGISTRY_KEY = 'themes.registry';

    public function clear(): void
    {
        Cache::forget(self::ACTIVE_THEME_KEY);
        Cache::forget(self::REGISTRY_KEY);

        try {
            View::flushFinderCache();

            if (! app()->runningUnitTests()) {
                Artisan::call('view:clear');
            }
        } catch (Throwable) {
            // Cache clearing should never leave activation half-finished.
        }
    }
}
