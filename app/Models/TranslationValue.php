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
])]
class TranslationValue extends Model
{
    public function source(): BelongsTo
    {
        return $this->belongsTo(TranslationSource::class, 'translation_source_id');
    }

    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }
}
