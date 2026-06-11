<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'actor_id',
    'subject_id',
    'event',
    'module',
    'action',
    'severity',
    'correlation_id',
    'ip_address',
    'user_agent',
    'target_type',
    'target_id',
    'target_url',
    'route_name',
    'request_method',
    'metadata',
])]
class UserAuditEvent extends Model
{
    protected $table = 'user_audit_events';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_id')->withTrashed();
    }

    protected function displayEvent(): Attribute
    {
        return Attribute::get(fn (): string => str($this->event)->replace(['.', '_'], ' ')->headline()->toString());
    }
}
