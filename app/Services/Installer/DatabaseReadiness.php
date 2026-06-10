<?php

namespace App\Services\Installer;

class DatabaseReadiness
{
    /**
     * @return array<string, array{label: string, driver: string, available: bool, primary: bool}>
     */
    public function connections(): array
    {
        return [
            'mysql' => [
                'label' => 'MySQL',
                'driver' => 'pdo_mysql',
                'available' => extension_loaded('pdo_mysql'),
                'primary' => true,
            ],
            'mariadb' => [
                'label' => 'MariaDB',
                'driver' => 'pdo_mysql',
                'available' => extension_loaded('pdo_mysql'),
                'primary' => true,
            ],
            'sqlite' => [
                'label' => 'SQLite',
                'driver' => 'pdo_sqlite',
                'available' => extension_loaded('pdo_sqlite'),
                'primary' => false,
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, passed: bool, detail: string}>
     */
    public function checklist(): array
    {
        return [
            [
                'label' => 'PHP 8.5 or newer',
                'passed' => version_compare(PHP_VERSION, '8.5.0', '>='),
                'detail' => 'Current version: '.PHP_VERSION,
            ],
            [
                'label' => 'Writable environment file',
                'passed' => is_writable(base_path()) && (file_exists(base_path('.env')) ? is_writable(base_path('.env')) : is_readable(base_path('.env.example'))),
                'detail' => file_exists(base_path('.env')) ? '.env is ready' : '.env will be created from .env.example',
            ],
            [
                'label' => 'Writable storage',
                'passed' => is_writable(storage_path()),
                'detail' => 'Needed for cache, sessions, logs, and install lock',
            ],
            [
                'label' => 'MySQL or MariaDB driver',
                'passed' => extension_loaded('pdo_mysql'),
                'detail' => extension_loaded('pdo_mysql') ? 'pdo_mysql is loaded' : 'Ask your host to enable pdo_mysql',
            ],
        ];
    }

    public function missingDriverMessage(string $connection): ?string
    {
        $connections = $this->connections();

        if (! isset($connections[$connection])) {
            return 'Choose a supported database connection.';
        }

        if (! $connections[$connection]['available']) {
            return "The {$connections[$connection]['driver']} PHP extension is not enabled for {$connections[$connection]['label']}.";
        }

        return null;
    }

    public function ready(): bool
    {
        return collect($this->checklist())->every(fn (array $item): bool => $item['passed']);
    }
}
