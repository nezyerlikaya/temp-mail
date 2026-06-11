<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'uuid',
    'overall_status',
    'summary',
    'results',
    'checked_by',
    'checked_at',
])]
class SystemHealthCheck extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'summary' => 'array',
            'results' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by')->withTrashed();
    }
}
