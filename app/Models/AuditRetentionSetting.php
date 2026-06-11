<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'retention_days',
    'preserve_critical',
    'updated_by',
])]
class AuditRetentionSetting extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'retention_days' => 'integer',
            'preserve_critical' => 'boolean',
            'updated_by' => 'integer',
        ];
    }
}
