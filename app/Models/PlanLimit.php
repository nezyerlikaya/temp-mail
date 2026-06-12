<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'plan_id',
    'maximum_active_inboxes',
    'inbox_lifetime_minutes',
    'maximum_messages_per_inbox',
    'maximum_message_size_kb',
    'custom_alias_allowed',
    'custom_domain_allowed',
    'api_access_allowed',
    'api_request_limit',
    'ads_enabled',
])]
class PlanLimit extends Model
{
    protected function casts(): array
    {
        return [
            'maximum_active_inboxes' => 'integer',
            'inbox_lifetime_minutes' => 'integer',
            'maximum_messages_per_inbox' => 'integer',
            'maximum_message_size_kb' => 'integer',
            'custom_alias_allowed' => 'boolean',
            'custom_domain_allowed' => 'boolean',
            'api_access_allowed' => 'boolean',
            'api_request_limit' => 'integer',
            'ads_enabled' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
