<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'scope',
    'scope_key',
    'usage',
    'font_family_slug',
    'fallback_stack',
    'updated_by',
])]
class FontAssignment extends Model
{
    protected function casts(): array
    {
        return [
            'fallback_stack' => 'array',
        ];
    }

    /** @return BelongsTo<FontFamily, $this> */
    public function fontFamily(): BelongsTo
    {
        return $this->belongsTo(FontFamily::class, 'font_family_slug', 'slug');
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
