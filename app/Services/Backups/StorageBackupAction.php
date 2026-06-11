<?php

namespace App\Services\Backups;

use Illuminate\Support\Facades\File;
use ZipArchive;

class StorageBackupAction
{
    /** @return array{files: int, bytes: int, source: string} */
    public function writeTo(ZipArchive $zip): array
    {
        $source = storage_path('app/public');
        $files = 0;
        $bytes = 0;

        if (! is_dir($source)) {
            $zip->addFromString('storage/.empty', 'No public upload storage directory exists yet.');

            return ['files' => 0, 'bytes' => 0, 'source' => $source];
        }

        foreach (File::allFiles($source) as $file) {
            $realPath = $file->getRealPath();

            if (! $realPath || str_contains($realPath, DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $zip->addFile($realPath, 'storage/uploads/'.$relative);
            $files++;
            $bytes += $file->getSize();
        }

        return ['files' => $files, 'bytes' => $bytes, 'source' => $source];
    }
}
