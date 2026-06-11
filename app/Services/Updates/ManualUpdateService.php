<?php

namespace App\Services\Updates;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class ManualUpdateService
{
    public function __construct(private readonly UpdatePackageVerifier $verifier) {}

    /**
     * @return array{path: string, verification: array<string, mixed>, manual_steps: array<int, string>}
     */
    public function storeAndVerify(UploadedFile $file, string $expectedChecksum, ?string $signature = null): array
    {
        $directory = storage_path('app/update-center/manual');
        File::ensureDirectoryExists($directory);

        $path = $directory.DIRECTORY_SEPARATOR.'manual-update-'.now()->format('YmdHis').'.zip';
        $file->move($directory, basename($path));

        return [
            'path' => $path,
            'verification' => $this->verifier->verify($path, $expectedChecksum, $signature),
            'manual_steps' => $this->manualSteps(),
        ];
    }

    /** @return array<int, string> */
    public function manualSteps(): array
    {
        return [
            'Keep the current backup available before touching application files.',
            'Upload the verified package outside the public web root.',
            'Extract only after confirming protected paths are absent.',
            'Run migrations with --force during the maintenance window.',
            'Clear application, route, and view caches after files are updated.',
        ];
    }
}
