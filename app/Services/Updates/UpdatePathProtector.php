<?php

namespace App\Services\Updates;

use App\Services\Installer\InstallState;
use ZipArchive;

class UpdatePathProtector
{
    public function __construct(private readonly InstallState $installState) {}

    /** @return array<int, string> */
    public function protectedPaths(): array
    {
        return [
            '.env',
            'storage/app/public',
            'storage/app/private',
            'storage/app/uploads',
            $this->relativeToBase($this->installState->lockPath()),
            $this->relativeToBase($this->installState->legacyLockPath()),
        ];
    }

    /** @return array<int, string> */
    public function validateArchive(string $zipPath): array
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('The update package is not a readable ZIP archive.');
        }

        $paths = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = (string) $zip->getNameIndex($index);

            if ($name === '' || str_ends_with($name, '/')) {
                continue;
            }

            $relative = $this->normalize($name);
            $this->assertSafeRelativePath($relative);
            $paths[] = $relative;
        }

        $zip->close();

        return $paths;
    }

    public function assertSafeRelativePath(string $relative): void
    {
        if ($relative === '' || str_contains($relative, "\0")) {
            throw new \RuntimeException('The update package contains an empty or invalid path.');
        }

        if (preg_match('/^[a-zA-Z]:/', $relative) === 1 || str_starts_with($relative, '/')) {
            throw new \RuntimeException('The update package contains an absolute path.');
        }

        if (str_contains($relative, '../') || $relative === '..' || str_ends_with($relative, '/..')) {
            throw new \RuntimeException('The update package contains a path traversal entry.');
        }

        foreach ($this->protectedPaths() as $protected) {
            if ($relative === $protected || str_starts_with($relative, $protected.'/')) {
                throw new \RuntimeException('The update package attempts to overwrite protected path: '.$protected.'.');
            }
        }
    }

    public function destinationFor(string $relative): string
    {
        $relative = $this->normalize($relative);
        $this->assertSafeRelativePath($relative);

        $base = realpath(base_path()) ?: base_path();
        $destination = $base.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $directory = realpath(dirname($destination)) ?: dirname($destination);

        if (! str_starts_with($directory, $base)) {
            throw new \RuntimeException('The update package resolves outside the application directory.');
        }

        return $destination;
    }

    public function normalize(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            $segments[] = $segment;
        }

        return implode('/', $segments);
    }

    private function relativeToBase(string $path): string
    {
        $base = str_replace('\\', '/', base_path());
        $path = str_replace('\\', '/', $path);

        return ltrim(str_replace($base, '', $path), '/');
    }
}
