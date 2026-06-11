<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'uuid',
    'original_name',
    'file_name',
    'disk',
    'path',
    'mime_type',
    'size_bytes',
    'width',
    'height',
    'type',
    'status',
    'alt_text',
    'title',
    'caption',
    'uploaded_by',
])]
class MediaAsset extends Model
{
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** @return HasMany<MediaUsage, $this> */
    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }
}
