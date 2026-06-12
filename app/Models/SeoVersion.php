<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'seo_record_id',
    'created_by',
    'snapshot',
    'reason',
])]
class SeoVersion extends Model
{
    protected function casts(): array
    {
        return [
            'seo_record_id' => 'integer',
            'created_by' => 'integer',
            'snapshot' => 'array',
        ];
    }

    /** @return BelongsTo<SeoRecord, $this> */
    public function record(): BelongsTo
    {
        return $this->belongsTo(SeoRecord::class, 'seo_record_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
