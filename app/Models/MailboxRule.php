<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'guest_lifetime_minutes', 'registered_lifetime_minutes', 'premium_lifetime_minutes',
    'maximum_active_mailboxes', 'maximum_messages_per_inbox', 'maximum_message_size_kb',
    'attachment_policy', 'auto_delete_expired', 'expired_cleanup_delay_hours',
    'inbox_refresh_rate_limit', 'random_alias_length', 'random_alias_format', 'updated_by',
])]
class MailboxRule extends Model
{
    protected function casts(): array
    {
        return ['auto_delete_expired' => 'boolean'];
    }
}
