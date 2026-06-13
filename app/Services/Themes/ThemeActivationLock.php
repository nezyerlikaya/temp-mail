<?php

namespace App\Services\Themes;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Throwable;

class ThemeActivationLock
{
    /** @return array{locked: bool, path: string, created_at: string|null, message: string} */
    public function status(): array
    {
        $path = $this->path();

        if (! File::exists($path)) {
            return [
                'locked' => false,
                'path' => $path,
                'created_at' => null,
                'message' => 'Theme activation is available.',
            ];
        }

        try {
            $createdAt = date('Y-m-d H:i:s', (int) File::lastModified($path));
        } catch (Throwable) {
            $createdAt = null;
        }

        return [
            'locked' => true,
            'path' => $path,
            'created_at' => $createdAt,
            'message' => 'Another theme activation is already running. Wait until the lock clears.',
        ];
    }

    public function acquire(string $owner): void
    {
        File::ensureDirectoryExists(dirname($this->path()));

        $handle = @fopen($this->path(), 'x');

        if ($handle === false) {
            throw new RuntimeException('Another theme activation is already running. Wait until the active lock clears.');
        }

        fwrite($handle, json_encode([
            'owner' => $owner,
            'created_at' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        fclose($handle);
    }

    public function release(): void
    {
        if (File::exists($this->path())) {
            File::delete($this->path());
        }
    }

    public function path(): string
    {
        return storage_path('app/theme-launch/theme-activation.lock');
    }
}
