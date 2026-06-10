<?php

namespace App\Actions\Installer;

use App\Services\Installer\DatabaseReadiness;
use PDO;
use PDOException;
use RuntimeException;

class TestDatabaseConnection
{
    public function __construct(private readonly DatabaseReadiness $readiness) {}

    /**
     * @param  array<string, string|null>  $credentials
     */
    public function handle(array $credentials): void
    {
        $connection = (string) ($credentials['connection'] ?? 'mysql');

        if ($message = $this->readiness->missingDriverMessage($connection)) {
            throw new RuntimeException($message);
        }

        try {
            if ($connection === 'sqlite') {
                new PDO('sqlite:'.($credentials['database'] ?? ''));

                return;
            }

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $credentials['host'] ?? 'localhost',
                $credentials['port'] ?? '3306',
                $credentials['database'] ?? ''
            );

            new PDO($dsn, $credentials['username'] ?? '', $credentials['password'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 8,
            ]);
        } catch (PDOException) {
            throw new RuntimeException('We could not connect to that database. Check the host, port, database name, username, and password, then try again.');
        }
    }
}
