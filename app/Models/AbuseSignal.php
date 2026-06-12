<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'signal_type',
    'severity',
    'source_module',
    'target_reference',
    'actor_user_id',
    'ip_hash',
    'occurrence_count',
    'first_seen_at',
    'last_seen_at',
    'status',
    'metadata',
    'deduplication_key',
    'reviewed_by',
    'resolved_at',
])]
class AbuseSignal extends Model
{
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurrence_count' => 'integer',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id')->withTrashed();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withTrashed();
    }
}
