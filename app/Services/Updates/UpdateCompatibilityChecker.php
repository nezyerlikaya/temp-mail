<?php

namespace App\Services\Updates;

use App\Services\Backups\DiskSpaceChecker;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateCompatibilityChecker
{
    public function __construct(private readonly DiskSpaceChecker $diskSpace) {}

    /**
     * @param  array<string, mixed>|null  $manifest
     * @return array{compatible: bool, results: array<int, array{id: string, label: string, status: string, message: string, detail: string|null}>}
     */
    public function check(?array $manifest = null): array
    {
        $results = [
            $this->phpVersion($manifest),
            $this->laravelVersion($manifest),
            ...$this->extensions($manifest),
            $this->storagePermissions(),
            $this->diskSpace(),
            $this->databaseConnection(),
        ];

        return [
            'compatible' => collect($results)->every(fn (array $result): bool => $result['status'] !== 'failed'),
            'results' => $results,
        ];
    }

    /** @param array<string, mixed>|null $manifest */
    private function phpVersion(?array $manifest): array
    {
        $minimum = (string) ($manifest['minimum_php'] ?? '8.5.0');
        $passed = version_compare(PHP_VERSION, $minimum, '>=');

        return $this->result('php', 'PHP version', $passed ? 'passed' : 'failed', 'Current PHP is '.PHP_VERSION.'.', 'Minimum required: '.$minimum.'.');
    }

    /** @param array<string, mixed>|null $manifest */
    private function laravelVersion(?array $manifest): array
    {
        $minimum = (string) ($manifest['minimum_laravel'] ?? '13.0.0');
        $current = Application::VERSION;
        $passed = version_compare($current, $minimum, '>=');

        return $this->result('laravel', 'Laravel version', $passed ? 'passed' : 'failed', 'Current Laravel is '.$current.'.', 'Minimum required: '.$minimum.'.');
    }

    /**
     * @param  array<string, mixed>|null  $manifest
     * @return array<int, array{id: string, label: string, status: string, message: string, detail: string|null}>
     */
    private function extensions(?array $manifest): array
    {
        $extensions = $manifest['required_extensions'] ?? config('updates.required_extensions', []);

        if (! is_array($extensions)) {
            $extensions = [];
        }

        return collect($extensions)
            ->map(fn (mixed $extension): string => (string) $extension)
            ->unique()
            ->values()
            ->map(function (string $extension): array {
                $loaded = extension_loaded($extension);

                return $this->result(
                    'extension-'.$extension,
                    $extension.' extension',
                    $loaded ? 'passed' : 'failed',
                    $loaded ? 'Loaded and available.' : 'Missing on this server.',
                    'Required before update installation can be considered.'
                );
            })
            ->all();
    }

    private function storagePermissions(): array
    {
        $path = storage_path('app');
        $writable = is_writable($path);

        return $this->result('storage', 'Storage permissions', $writable ? 'passed' : 'failed', $writable ? 'Storage is writable.' : 'Storage is not writable.', $path);
    }

    private function diskSpace(): array
    {
        $status = $this->diskSpace->status();

        return $this->result(
            'disk-space',
            'Disk space readiness',
            $status['enough'] ? 'passed' : 'failed',
            $status['enough'] ? 'Enough free disk space is available.' : 'Free disk space is below the safe minimum.',
            $this->bytes((float) $status['free_bytes']).' free of '.$this->bytes((float) $status['total_bytes']).'.'
        );
    }

    private function databaseConnection(): array
    {
        try {
            DB::connection()->getPdo();

            return $this->result('database', 'Database connection', 'passed', 'Database connection is available.', config('database.default').' connection.');
        } catch (Throwable) {
            return $this->result('database', 'Database connection', 'failed', 'Database connection is not available.', 'Repair database access before checking install readiness.');
        }
    }

    private function result(string $id, string $label, string $status, string $message, ?string $detail): array
    {
        return compact('id', 'label', 'status', 'message', 'detail');
    }

    private function bytes(float $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return number_format($bytes / 1024 / 1024 / 1024, 2).' GB';
        }

        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / 1024 / 1024, 2).' MB';
        }

        return number_format($bytes / 1024, 2).' KB';
    }
}
