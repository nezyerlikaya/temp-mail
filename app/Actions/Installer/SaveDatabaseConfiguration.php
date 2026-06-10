<?php

namespace App\Actions\Installer;

use App\Services\Installer\EnvironmentManager;

class SaveDatabaseConfiguration
{
    public function __construct(private readonly EnvironmentManager $environment) {}

    /**
     * @param  array<string, string|null>  $credentials
     */
    public function handle(array $credentials): void
    {
        $values = [
            'DB_CONNECTION' => $credentials['connection'],
            'DB_DATABASE' => $credentials['database'],
            'SESSION_DRIVER' => 'file',
            'CACHE_STORE' => 'file',
            'QUEUE_CONNECTION' => 'sync',
        ];

        if (($credentials['connection'] ?? null) !== 'sqlite') {
            $values += [
                'DB_HOST' => $credentials['host'],
                'DB_PORT' => $credentials['port'],
                'DB_USERNAME' => $credentials['username'],
                'DB_PASSWORD' => $credentials['password'] ?? '',
            ];
        }

        $this->environment->write($values);
    }
}
