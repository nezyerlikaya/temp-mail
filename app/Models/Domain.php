<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'domain_name',
    'display_name',
    'is_active',
    'is_public',
    'catch_all_ready',
    'is_default',
    'sort_order',
    'status',
    'dns_checks',
    'last_checked_at',
    'created_by',
    'updated_by',
])]
class Domain extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'catch_all_ready' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
            'dns_checks' => 'array',
            'last_checked_at' => 'datetime',
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

    public function inboundMailConnections(): HasMany
    {
        return $this->hasMany(InboundMailConnection::class);
    }

    public function smtpConnections(): HasMany
    {
        return $this->hasMany(SmtpConnection::class);
    }
}
