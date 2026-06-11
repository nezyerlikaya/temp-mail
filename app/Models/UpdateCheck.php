<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'uuid',
    'channel',
    'current_version',
    'latest_version',
    'status',
    'endpoint',
    'https_endpoint',
    'signed_manifest',
    'checksum',
    'signature',
    'manifest',
    'compatibility',
    'error_message',
    'checked_by',
    'checked_at',
])]
class UpdateCheck extends Model
{
    protected function casts(): array
    {
        return [
            'https_endpoint' => 'boolean',
            'signed_manifest' => 'boolean',
            'manifest' => 'array',
            'compatibility' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
