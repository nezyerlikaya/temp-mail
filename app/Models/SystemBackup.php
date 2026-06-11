<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'uuid',
    'type',
    'status',
    'disk',
    'path',
    'filename',
    'size_bytes',
    'checksum',
    'manifest',
    'failure_reason',
    'created_by',
    'completed_at',
])]
class SystemBackup extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'manifest' => 'array',
            'size_bytes' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
