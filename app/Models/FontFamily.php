<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'slug',
    'name',
    'css_family',
    'provider',
    'category',
    'supported_scripts',
    'rtl_support',
    'available_weights',
    'font_display',
    'is_active',
    'local_file_ready',
    'media_ready',
    'metadata',
    'updated_by',
])]
class FontFamily extends Model
{
    protected function casts(): array
    {
        return [
            'supported_scripts' => 'array',
            'available_weights' => 'array',
            'rtl_support' => 'boolean',
            'is_active' => 'boolean',
            'local_file_ready' => 'boolean',
            'media_ready' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
