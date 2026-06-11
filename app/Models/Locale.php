<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'language_name',
    'native_name',
    'locale',
    'direction',
    'region',
    'market_readiness',
    'is_active',
    'is_default',
    'sort_order',
    'launch_status',
    'readiness',
    'updated_by',
])]
class Locale extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
            'readiness' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
