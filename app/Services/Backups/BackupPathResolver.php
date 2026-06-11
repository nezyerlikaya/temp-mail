<?php

namespace App\Services\Backups;

use App\Models\SystemBackup;

class BackupPathResolver
{
    public function directory(): string
    {
        return storage_path('app/backups');
    }

    public function absolutePath(SystemBackup $backup): string
    {
        $relative = trim((string) $backup->path, '/\\');

        if ($relative === '' || $relative !== str_replace(['..', "\0"], '', $relative)) {
            throw new \RuntimeException('Invalid backup path.');
        }

        $fullPath = $this->directory().DIRECTORY_SEPARATOR.$relative;
        $directory = realpath(dirname($fullPath)) ?: dirname($fullPath);
        $root = realpath($this->directory()) ?: $this->directory();

        if (! str_starts_with($directory, $root)) {
            throw new \RuntimeException('Invalid backup path.');
        }

        return $fullPath;
    }

    public function assertPrivate(): void
    {
        $root = realpath($this->directory()) ?: $this->directory();
        $public = realpath(public_path()) ?: public_path();

        if (str_starts_with($root, $public)) {
            throw new \RuntimeException('Backup storage is inside the public web root.');
        }
    }
}
