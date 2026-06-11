<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use Illuminate\Support\Facades\Storage;

class MediaUrlResolver
{
    public function url(MediaAsset $asset): string
    {
        return Storage::disk($asset->disk)->url($asset->path);
    }

    public function displaySize(MediaAsset $asset): string
    {
        if ($asset->size_bytes >= 1024 * 1024) {
            return number_format($asset->size_bytes / 1024 / 1024, 2).' MB';
        }

        return number_format($asset->size_bytes / 1024, 2).' KB';
    }
}
