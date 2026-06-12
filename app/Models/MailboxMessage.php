<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'mailbox_id', 'sender_email', 'sender_name', 'subject', 'preview_text', 'plain_text_body',
    'sanitized_html_body', 'raw_headers', 'attachment_count', 'message_size', 'received_at',
    'read_at', 'deleted_at', 'quarantined_at',
])]
class MailboxMessage extends Model
{
    protected function casts(): array
    {
        return [
            'raw_headers' => 'array',
            'attachment_count' => 'integer',
            'message_size' => 'integer',
            'received_at' => 'datetime',
            'read_at' => 'datetime',
            'deleted_at' => 'datetime',
            'quarantined_at' => 'datetime',
        ];
    }

    public function mailbox(): BelongsTo
    {
        return $this->belongsTo(Mailbox::class);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }
}
