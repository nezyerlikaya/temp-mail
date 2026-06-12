<?php

namespace App\Models;

use Database\Factories\SystemNotificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'recipient_user_id',
    'event_key',
    'type',
    'severity',
    'title',
    'message',
    'related_module',
    'target_type',
    'target_id',
    'action_route',
    'action_parameters',
    'action_url',
    'read_at',
    'archived_at',
    'occurrence_count',
    'first_occurred_at',
    'last_occurred_at',
    'snoozed_until',
    'deduplication_key',
    'digest_pending_at',
    'digest_sent_at',
    'digest_status',
    'email_attempted_at',
    'email_sent_at',
    'email_status',
])]
class SystemNotification extends Model
{
    /** @use HasFactory<SystemNotificationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'action_parameters' => 'array',
            'read_at' => 'datetime',
            'archived_at' => 'datetime',
            'occurrence_count' => 'integer',
            'first_occurred_at' => 'datetime',
            'last_occurred_at' => 'datetime',
            'snoozed_until' => 'datetime',
            'digest_pending_at' => 'datetime',
            'digest_sent_at' => 'datetime',
            'email_attempted_at' => 'datetime',
            'email_sent_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, SystemNotification> */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }
}
