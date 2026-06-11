<?php

namespace App\Actions\Installer;

use App\Models\User;
use App\Services\Installer\EnvironmentManager;
use App\Services\Installer\InstallState;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

class CompleteInstallation
{
    public function __construct(
        private readonly InstallState $installState,
        private readonly EnvironmentManager $environment,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string}  $admin
     */
    public function handle(array $admin): void
    {
        $previousLimit = ini_get('max_execution_time');
        set_time_limit(300);

        try {
            Artisan::call('config:clear');
            $this->refreshDatabaseConfig();
            Artisan::call('migrate', ['--force' => true]);

            User::query()->create([
                ...$admin,
                'display_name' => $admin['name'],
                'status' => 'active',
                'role' => 'admin',
                'is_admin' => true,
            ]);

            $this->installState->lock();
        } catch (Throwable $throwable) {
            report($throwable);

            throw $throwable;
        } finally {
            if (is_numeric($previousLimit)) {
                set_time_limit((int) $previousLimit);
            }
        }
    }

    private function refreshDatabaseConfig(): void
    {
        $connection = $this->environment->value('DB_CONNECTION') ?: 'mysql';

        config(['database.default' => $connection]);

        if ($connection === 'sqlite') {
            config(['database.connections.sqlite.database' => $this->environment->value('DB_DATABASE')]);
        } else {
            config([
                "database.connections.{$connection}.host" => $this->environment->value('DB_HOST') ?: 'localhost',
                "database.connections.{$connection}.port" => $this->environment->value('DB_PORT') ?: '3306',
                "database.connections.{$connection}.database" => $this->environment->value('DB_DATABASE') ?: '',
                "database.connections.{$connection}.username" => $this->environment->value('DB_USERNAME') ?: '',
                "database.connections.{$connection}.password" => $this->environment->value('DB_PASSWORD') ?: '',
            ]);
        }

        DB::purge($connection);
    }
}
