<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'domain_id',
    'name',
    'host',
    'port',
    'encryption',
    'username',
    'encrypted_password',
    'mailbox',
    'connection_timeout',
    'validate_certificate',
    'is_active',
    'status',
    'last_test_result',
    'health_history',
    'last_tested_at',
    'created_by',
    'updated_by',
])]
#[Hidden(['encrypted_password'])]
class InboundMailConnection extends Model
{
    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'encrypted_password' => 'encrypted',
            'connection_timeout' => 'integer',
            'validate_certificate' => 'boolean',
            'is_active' => 'boolean',
            'last_test_result' => 'array',
            'health_history' => 'array',
            'last_tested_at' => 'datetime',
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }
}
