<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    'related_comment_id',
    'related_url',
    'evidence_media_ids',
    'assigned_to',
    'internal_notes',
    'submitted_ip_hash',
    'bot_protection_readiness',
    'reporter_notification_status',
    'resolution_outcome',
    'resolution_reason',
    'resolution_summary',
    'resolved_by',
    'resolved_at',
    'reopened_at',
    'archived_at',
    'retention_review_at',
    'reporter_response_subject',
    'reporter_response_body',
    'reporter_response_prepared_at',
])]
class AbuseReport extends Model
{
    protected function casts(): array
    {
        return [
            'evidence_media_ids' => 'array',
            'bot_protection_readiness' => 'array',
            'resolved_at' => 'datetime',
            'reopened_at' => 'datetime',
            'archived_at' => 'datetime',
            'retention_review_at' => 'datetime',
            'reporter_response_prepared_at' => 'datetime',
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

    public function relatedComment(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'related_comment_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by')->withTrashed();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(AbuseCaseNote::class)->latest();
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(AbuseEvidence::class)->latest();
    }

    public function events(): HasMany
    {
        return $this->hasMany(AbuseCaseEvent::class)->latest();
    }

    public function getRouteKeyName(): string
    {
        return 'case_reference';
    }
}
