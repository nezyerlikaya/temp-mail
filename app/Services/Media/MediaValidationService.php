<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;

class MediaValidationService
{
    /** @return array<int, string> */
    public function allowedMimeTypes(): array
    {
        return [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'image/svg+xml',
            'application/pdf',
        ];
    }

    public function maxKilobytes(): int
    {
        return 10 * 1024;
    }

    public function typeFor(string $mimeType): string
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => 'image',
            $mimeType === 'application/pdf' => 'document',
            default => 'document',
        };
    }

    /** @return array{width: int|null, height: int|null} */
    public function dimensions(UploadedFile $file): array
    {
        if (! str_starts_with((string) $file->getMimeType(), 'image/') || $file->getMimeType() === 'image/svg+xml') {
            return ['width' => null, 'height' => null];
        }

        $size = @getimagesize($file->getRealPath());

        return [
            'width' => is_array($size) ? (int) ($size[0] ?? 0) ?: null : null,
            'height' => is_array($size) ? (int) ($size[1] ?? 0) ?: null : null,
        ];
    }

    public function assertSafeDisk(string $disk): void
    {
        $root = config("filesystems.disks.{$disk}.root");
        $public = public_path();

        if (! is_string($root) || $root === '') {
            throw new \RuntimeException('Media storage disk is not configured.');
        }

        $resolvedRoot = realpath($root) ?: $root;
        $resolvedPublic = realpath($public) ?: $public;

        if ($disk !== 'public' || str_starts_with($resolvedRoot, $resolvedPublic)) {
            throw new \RuntimeException('Media storage must use the Laravel public disk outside direct public paths.');
        }
    }
}
