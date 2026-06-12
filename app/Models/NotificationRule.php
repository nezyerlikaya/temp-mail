<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'event_key',
    'severity',
    'in_app_enabled',
    'email_enabled',
    'recipient_roles',
    'digest_mode',
    'quiet_hours_enabled',
    'quiet_hours_start',
    'quiet_hours_end',
    'is_active',
])]
class NotificationRule extends Model
{
    protected function casts(): array
    {
        return [
            'in_app_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'recipient_roles' => 'array',
            'quiet_hours_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }
}
