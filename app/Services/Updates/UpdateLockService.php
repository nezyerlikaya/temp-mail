<?php

namespace App\Services\Updates;

use Illuminate\Support\Facades\File;
use Throwable;

class UpdateLockService
{
    /** @return array{locked: bool, path: string, message: string, created_at: string|null} */
    public function status(): array
    {
        $path = $this->path();

        if (! File::exists($path)) {
            return [
                'locked' => false,
                'path' => $path,
                'message' => 'No update lock is active.',
                'created_at' => null,
            ];
        }

        $createdAt = null;

        try {
            $createdAt = date('Y-m-d H:i:s', (int) File::lastModified($path));
        } catch (Throwable) {
            $createdAt = null;
        }

        return [
            'locked' => true,
            'path' => $path,
            'message' => 'An update lock exists. Install actions must wait until it is cleared.',
            'created_at' => $createdAt,
        ];
    }

    public function isLocked(): bool
    {
        return File::exists($this->path());
    }

    private function path(): string
    {
        return storage_path('app/update-center/update.lock');
    }
}
