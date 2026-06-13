<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'api_key_id',
    'user_id',
    'key_prefix',
    'endpoint',
    'method',
    'response_status',
    'duration_ms',
    'requested_at',
])]
class ApiRequestLog extends Model
{
    protected function casts(): array
    {
        return ['response_status' => 'integer', 'duration_ms' => 'integer', 'requested_at' => 'datetime'];
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
