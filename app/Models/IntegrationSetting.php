<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'integration_key',
    'environment',
    'is_active',
    'connection_status',
    'payload',
    'encrypted_secrets',
    'test_history',
    'last_tested_at',
    'updated_by',
])]
#[Hidden(['encrypted_secrets'])]
class IntegrationSetting extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'test_history' => 'array',
            'is_active' => 'boolean',
            'last_tested_at' => 'datetime',
            'updated_by' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }
}
