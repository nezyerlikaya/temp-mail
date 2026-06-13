<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'theme_slug',
    'version_number',
    'tokens',
    'contrast_report',
    'published_by',
    'source_version_id',
])]
class AppearanceVersion extends Model
{
    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'tokens' => 'array',
            'contrast_report' => 'array',
            'published_by' => 'integer',
            'source_version_id' => 'integer',
        ];
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function sourceVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_version_id');
    }
}
