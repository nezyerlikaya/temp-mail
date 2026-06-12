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
