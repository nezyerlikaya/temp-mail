<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'translation_source_id',
    'locale_id',
    'value',
    'status',
    'updated_by',
    'reviewed_by',
    'reviewed_at',
    'published_by',
    'published_at',
])]
class TranslationValue extends Model
{
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(TranslationSource::class, 'translation_source_id');
    }

    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }
}
