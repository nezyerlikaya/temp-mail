<?php

namespace App\Services\Settings;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Throwable;

class SystemReadinessService
{
    /** @return array<int, array{label: string, status: string, detail: string}> */
    public function statuses(): array
    {
        return [
            $this->status('Environment', 'ready', app()->environment()),
            $this->status('Application version', 'ready', (string) config('app.version', '1.0.0')),
            $this->storageStatus(),
            $this->cacheStatus(),
            $this->status('Scheduler', 'attention', File::exists(base_path('routes/console.php'))
                ? 'Schedule routes are ready. Verify that server cron runs artisan schedule:run every minute.'
                : 'Schedule routes are missing.'),
            $this->status('Queue', config('queue.default') === 'sync' ? 'attention' : 'ready', 'Driver: '.config('queue.default').'. Configure a worker for asynchronous jobs.'),
        ];
    }

    private function storageStatus(): array
    {
        return $this->status('Storage', is_writable(storage_path()) ? 'ready' : 'blocked', storage_path());
    }

    private function cacheStatus(): array
    {
        try {
            Cache::put('settings-readiness-probe', 'ready', 10);
            $ready = Cache::get('settings-readiness-probe') === 'ready';
            Cache::forget('settings-readiness-probe');

            return $this->status('Cache', $ready ? 'ready' : 'blocked', 'Store: '.config('cache.default'));
        } catch (Throwable) {
            return $this->status('Cache', 'blocked', 'The configured cache store is not writable.');
        }
    }

    /** @return array{label: string, status: string, detail: string} */
    private function status(string $label, string $status, string $detail): array
    {
        return compact('label', 'status', 'detail');
    }
}
