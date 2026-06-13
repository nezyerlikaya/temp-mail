<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'theme_slug',
    'mode',
    'draft_tokens',
    'published_tokens',
    'published_at',
    'published_by',
    'updated_by',
])]
class AppearanceSetting extends Model
{
    protected function casts(): array
    {
        return [
            'draft_tokens' => 'array',
            'published_tokens' => 'array',
            'published_at' => 'datetime',
            'published_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
