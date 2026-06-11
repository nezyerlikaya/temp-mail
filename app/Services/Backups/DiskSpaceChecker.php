<?php

namespace App\Services\Backups;

class DiskSpaceChecker
{
    private const MINIMUM_FREE_BYTES = 20 * 1024 * 1024;

    /** @return array{path: string, free_bytes: float, total_bytes: float, minimum_free_bytes: int, enough: bool} */
    public function status(): array
    {
        $path = storage_path('app');
        $free = (float) @disk_free_space($path);
        $total = (float) @disk_total_space($path);

        return [
            'path' => $path,
            'free_bytes' => $free,
            'total_bytes' => $total,
            'minimum_free_bytes' => self::MINIMUM_FREE_BYTES,
            'enough' => $free >= self::MINIMUM_FREE_BYTES,
        ];
    }

    public function assertEnough(): void
    {
        if (! $this->status()['enough']) {
            throw new \RuntimeException('The server does not have enough free disk space to create a backup safely.');
        }
    }
}
