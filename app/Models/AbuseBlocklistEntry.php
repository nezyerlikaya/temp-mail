<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['abuse_report_id', 'type', 'value_hash', 'encrypted_value', 'value_preview', 'status', 'created_by'])]
class AbuseBlocklistEntry extends Model
{
    protected function casts(): array
    {
        return ['encrypted_value' => 'encrypted'];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(AbuseReport::class, 'abuse_report_id');
    }
}
