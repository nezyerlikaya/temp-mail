<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'group_key',
    'translation_key',
    'source_value',
    'description',
    'value_type',
    'is_required',
    'is_active',
    'sort_order',
    'created_by',
    'updated_by',
])]
class TranslationSource extends Model
{
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(TranslationValue::class);
    }
}
