<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'entry_type',
    'normalized_hash',
    'encrypted_normalized_value',
    'display_value',
    'reason',
    'source',
    'status',
    'starts_at',
    'expires_at',
    'created_by',
    'updated_by',
    'related_abuse_report_id',
    'notes',
])]
class BlockedListEntry extends Model
{
    protected function casts(): array
    {
        return [
            'encrypted_normalized_value' => 'encrypted',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    public function abuseReport(): BelongsTo
    {
        return $this->belongsTo(AbuseReport::class, 'related_abuse_report_id');
    }
}
