<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['api_key_id', 'user_id', 'usage_date', 'request_count'])]
class ApiUsageEvent extends Model
{
    protected function casts(): array
    {
        return ['usage_date' => 'date', 'request_count' => 'integer'];
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
