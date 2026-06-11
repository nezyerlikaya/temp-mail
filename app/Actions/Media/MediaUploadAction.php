<?php

namespace App\Actions\Media;

use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Media\MediaValidationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaUploadAction
{
    public function __construct(
        private readonly MediaValidationService $validation,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $metadata */
    public function handle(User $actor, UploadedFile $file, array $metadata): MediaAsset
    {
        $disk = 'public';
        $this->validation->assertSafeDisk($disk);

        $uuid = (string) Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $fileName = $uuid.'.'.$extension;
        $path = $file->storeAs('media/'.now()->format('Y/m'), $fileName, $disk);

        if (! is_string($path)) {
            throw new \RuntimeException('The media upload could not be stored.');
        }

        $dimensions = $this->validation->dimensions($file);
        $mimeType = (string) $file->getMimeType();

        $asset = MediaAsset::query()->create([
            'uuid' => $uuid,
            'original_name' => $file->getClientOriginalName(),
            'file_name' => $fileName,
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $mimeType,
            'size_bytes' => (int) $file->getSize(),
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'type' => $metadata['type'] ?? $this->validation->typeFor($mimeType),
            'status' => $metadata['status'] ?? 'active',
            'alt_text' => $metadata['alt_text'] ?? null,
            'title' => $metadata['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'caption' => $metadata['caption'] ?? null,
            'uploaded_by' => $actor->id,
        ]);

        $this->audit->record('media.uploaded', $actor, null, [
            'media_uuid' => $asset->uuid,
            'type' => $asset->type,
            'mime_type' => $asset->mime_type,
            'size_bytes' => $asset->size_bytes,
        ], ['module' => 'media', 'action' => 'Upload media', 'severity' => 'info']);

        return $asset;
    }
}
