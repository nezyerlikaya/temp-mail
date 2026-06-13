<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'slug',
    'status',
    'last_activated_at',
    'last_deactivated_at',
    'activated_by',
])]
class ThemeState extends Model
{
    protected function casts(): array
    {
        return [
            'last_activated_at' => 'datetime',
            'last_deactivated_at' => 'datetime',
            'activated_by' => 'integer',
        ];
    }

    public function activator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by');
    }
}
