<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'group',
    'payload',
    'encrypted_secrets',
    'test_history',
    'last_tested_at',
    'last_test_status',
    'updated_by',
])]
class SecuritySetting extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'test_history' => 'array',
            'last_tested_at' => 'datetime',
            'updated_by' => 'integer',
        ];
    }
}
