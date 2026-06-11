<?php

namespace App\Services\Installer;

class InstallState
{
    public function lockPath(): string
    {
        return storage_path('app/installed.lock');
    }

    public function legacyLockPath(): string
    {
        return storage_path('app/install.lock');
    }

    public function recoveryPath(): string
    {
        return storage_path('app/installer-recovery.flag');
    }

    public function isRecoveringEnvironment(): bool
    {
        return file_exists($this->recoveryPath());
    }

    public function isInstalled(): bool
    {
        if ($this->isRecoveringEnvironment()) {
            return false;
        }

        return file_exists($this->lockPath()) || file_exists($this->legacyLockPath());
    }

    public function lock(): void
    {
        $directory = dirname($this->lockPath());

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($this->lockPath(), 'installed_at='.now()->toIso8601String().PHP_EOL, LOCK_EX);

        if (file_exists($this->recoveryPath())) {
            unlink($this->recoveryPath());
        }
    }
}
