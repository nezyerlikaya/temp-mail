<?php

namespace App\Services\Backups;

use App\Models\SystemBackup;
use ZipArchive;

class BackupIntegrityChecker
{
    public function __construct(private readonly BackupPathResolver $paths) {}

    /** @return array{status: string, message: string} */
    public function check(SystemBackup $backup): array
    {
        if ($backup->status !== 'completed') {
            return ['status' => 'failed', 'message' => 'Backup did not complete.'];
        }

        $path = $this->paths->absolutePath($backup);

        if (! is_file($path)) {
            return ['status' => 'failed', 'message' => 'Backup file is missing.'];
        }

        if ($backup->checksum && hash_file('sha256', $path) !== $backup->checksum) {
            return ['status' => 'failed', 'message' => 'Checksum mismatch.'];
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            return ['status' => 'failed', 'message' => 'Archive cannot be opened.'];
        }

        $hasManifest = $zip->locateName('manifest.json') !== false;
        $zip->close();

        return $hasManifest
            ? ['status' => 'passed', 'message' => 'Archive and checksum verified.']
            : ['status' => 'failed', 'message' => 'Manifest file is missing.'];
    }
}
