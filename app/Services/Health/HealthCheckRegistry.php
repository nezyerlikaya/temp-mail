<?php

namespace App\Services\Health;

use App\Services\Backups\DiskSpaceChecker;
use App\Services\Installer\InstallState;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HealthCheckRegistry
{
    public function __construct(
        private readonly DiskSpaceChecker $diskSpace,
        private readonly InstallState $installState,
    ) {}

    /** @return array<int, array{id: string, label: string, group: string, status: string, message: string, detail: string}> */
    public function checks(): array
    {
        return [
            $this->phpVersion(),
            $this->laravelVersion(),
            $this->database(),
            $this->storageWritable(),
            $this->cacheWritable(),
            $this->queueReadiness(),
            $this->schedulerReadiness(),
            $this->smtpReadiness(),
            $this->domainMxReadiness(),
            $this->diskFreeSpace(),
            $this->appKey(),
            $this->installedLock(),
            $this->maintenanceMode(),
            $this->latestUpdateReadiness(),
        ];
    }

    /** @return array{id: string, label: string, group: string, status: string, message: string, detail: string} */
    private function phpVersion(): array
    {
        return $this->result(
            'php-version',
            'PHP version',
            'Runtime',
            version_compare(PHP_VERSION, '8.5.0', '>=') ? 'healthy' : 'critical',
            'Running PHP '.PHP_VERSION.'.',
            'PHP 8.5+ is required by the project blueprint.'
        );
    }

    private function laravelVersion(): array
    {
        return $this->result('laravel-version', 'Laravel version', 'Runtime', 'healthy', 'Running Laravel '.app()->version().'.', 'Framework booted successfully.');
    }

    private function database(): array
    {
        try {
            DB::connection()->getPdo();
            $tables = count(Schema::getTables());

            return $this->result('database-connection', 'Database connection', 'Data', 'healthy', 'Database connection succeeded.', $tables.' tables visible.');
        } catch (Throwable) {
            return $this->result('database-connection', 'Database connection', 'Data', 'critical', 'Database connection failed.', 'Check configured database host, credentials, and driver.');
        }
    }

    private function storageWritable(): array
    {
        return $this->writable('storage-writable', 'Storage writable', storage_path('app'), 'Filesystem');
    }

    private function cacheWritable(): array
    {
        try {
            Cache::put('health-check-probe', now()->toIso8601String(), 30);
            Cache::forget('health-check-probe');

            return $this->result('cache-writable', 'Cache writable', 'Filesystem', 'healthy', 'Cache write probe succeeded.', 'Application cache can store temporary health data.');
        } catch (Throwable) {
            return $this->result('cache-writable', 'Cache writable', 'Filesystem', 'critical', 'Cache write probe failed.', 'Check storage/framework/cache permissions.');
        }
    }

    private function queueReadiness(): array
    {
        $connection = (string) config('queue.default', 'sync');

        return $this->result('queue-readiness', 'Queue readiness', 'Operations', $connection === 'sync' ? 'attention' : 'healthy', 'Queue connection is '.$connection.'.', $connection === 'sync' ? 'Shared-hosting friendly sync queue is ready, but background workers are not configured.' : 'Queue connection is configured for async processing.');
    }

    private function schedulerReadiness(): array
    {
        $path = storage_path('app/scheduler-last-run.txt');

        if (is_file($path)) {
            return $this->result('scheduler-last-run', 'Scheduler last run', 'Operations', 'healthy', 'Scheduler marker exists.', 'Last marker: '.date('M j, Y H:i', filemtime($path)));
        }

        return $this->result('scheduler-last-run', 'Scheduler last run', 'Operations', 'attention', 'Scheduler marker is not present yet.', 'Cron wiring readiness only; full scheduler management is out of scope.');
    }

    private function smtpReadiness(): array
    {
        $mailer = (string) config('mail.default', 'log');

        return $this->result('smtp-readiness', 'SMTP/mail readiness', 'Messaging', in_array($mailer, ['smtp', 'ses', 'postmark', 'mailgun'], true) ? 'healthy' : 'attention', 'Mail transport is '.$mailer.'.', 'No SMTP secret values are displayed or tested by sending mail.');
    }

    private function domainMxReadiness(): array
    {
        return $this->result('domain-mx-readiness', 'Domain MX readiness', 'Mail Infrastructure', 'attention', 'Domains module is not connected yet.', 'MX summary is a readiness placeholder until Domains is implemented.');
    }

    private function diskFreeSpace(): array
    {
        $status = $this->diskSpace->status();

        return $this->result('disk-free-space', 'Disk free space', 'Filesystem', $status['enough'] ? 'healthy' : 'critical', number_format($status['free_bytes'] / 1024 / 1024, 0).' MB free.', 'Minimum required free space: '.number_format($status['minimum_free_bytes'] / 1024 / 1024, 0).' MB.');
    }

    private function appKey(): array
    {
        $exists = filled(config('app.key'));

        return $this->result('app-key', 'APP_KEY exists', 'Security', $exists ? 'healthy' : 'critical', $exists ? 'APP_KEY is configured.' : 'APP_KEY is missing.', 'Secret value is intentionally hidden.');
    }

    private function installedLock(): array
    {
        $installed = $this->installState->isInstalled();

        return $this->result('installed-lock', 'Installer lock', 'Security', $installed ? 'healthy' : 'critical', $installed ? 'Installed lock exists.' : 'Installed lock is missing.', 'Missing lock can reopen installer recovery flows.');
    }

    private function maintenanceMode(): array
    {
        return $this->result('maintenance-mode', 'Maintenance mode', 'Operations', app()->isDownForMaintenance() ? 'attention' : 'healthy', app()->isDownForMaintenance() ? 'Application maintenance mode is active.' : 'Application maintenance mode is inactive.', 'Admins remain able to access dashboard routes.');
    }

    private function latestUpdateReadiness(): array
    {
        return $this->result('latest-update-check', 'Latest update check', 'System', 'attention', 'Update Center is not connected yet.', 'Readiness placeholder for future update status.');
    }

    private function writable(string $id, string $label, string $path, string $group): array
    {
        try {
            File::ensureDirectoryExists($path);
            $probe = $path.DIRECTORY_SEPARATOR.'health-check.tmp';
            File::put($probe, 'ok');
            File::delete($probe);

            return $this->result($id, $label, $group, 'healthy', $label.' check succeeded.', $path);
        } catch (Throwable) {
            return $this->result($id, $label, $group, 'critical', $label.' check failed.', 'Check filesystem permissions.');
        }
    }

    /** @return array{id: string, label: string, group: string, status: string, message: string, detail: string} */
    private function result(string $id, string $label, string $group, string $status, string $message, string $detail): array
    {
        return compact('id', 'label', 'group', 'status', 'message', 'detail');
    }
}
