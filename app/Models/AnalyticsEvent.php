<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'event_key',
    'user_id',
    'locale_id',
    'domain_id',
    'visitor_hash',
    'session_hash',
    'ip_hash',
    'metadata',
])]
class AnalyticsEvent extends Model
{
    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
