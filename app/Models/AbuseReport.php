<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'case_reference',
    'report_type',
    'priority',
    'status',
    'reporter_name',
    'reporter_email',
    'reporter_email_hash',
    'subject',
    'description',
    'description_excerpt',
    'reported_mailbox_id',
    'reported_domain_id',
    'reported_user_id',
    'related_url',
    'evidence_media_ids',
    'assigned_to',
    'internal_notes',
    'submitted_ip_hash',
    'bot_protection_readiness',
    'reporter_notification_status',
])]
class AbuseReport extends Model
{
    protected function casts(): array
    {
        return [
            'evidence_media_ids' => 'array',
            'bot_protection_readiness' => 'array',
        ];
    }

    public function mailbox(): BelongsTo
    {
        return $this->belongsTo(Mailbox::class, 'reported_mailbox_id');
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'reported_domain_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id')->withTrashed();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to')->withTrashed();
    }

    public function getRouteKeyName(): string
    {
        return 'case_reference';
    }
}
