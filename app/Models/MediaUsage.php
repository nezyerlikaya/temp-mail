<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'media_asset_id',
    'module',
    'usage_context',
    'slot',
    'usable_type',
    'usable_id',
    'label',
    'metadata',
    'attached_by',
])]
class MediaUsage extends Model
{
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'attached_by' => 'integer',
        ];
    }

    /** @return BelongsTo<MediaAsset, $this> */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attached_by');
    }
}
