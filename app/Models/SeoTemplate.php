<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'target_type',
    'locale_id',
    'name',
    'meta_title_template',
    'meta_description_template',
    'og_title_template',
    'og_description_template',
    'schema_type',
    'schema_json_template',
    'is_active',
    'updated_by',
])]
class SeoTemplate extends Model
{
    protected function casts(): array
    {
        return [
            'locale_id' => 'integer',
            'schema_json_template' => 'array',
            'is_active' => 'boolean',
            'updated_by' => 'integer',
        ];
    }

    /** @return BelongsTo<Locale, $this> */
    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }
}
