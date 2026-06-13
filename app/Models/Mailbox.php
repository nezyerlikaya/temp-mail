<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'domain_id',
    'user_id',
    'locale_id',
    'address',
    'local_part',
    'mailbox_type',
    'status',
    'expires_at',
    'last_activity_at',
    'message_count',
    'created_ip_hash',
    'activity_timeline',
    'created_by',
    'api_key_id',
    'api_environment',
])]
#[Hidden(['created_ip_hash'])]
class Mailbox extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'message_count' => 'integer',
            'activity_timeline' => 'array',
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MailboxMessage::class);
    }
}
