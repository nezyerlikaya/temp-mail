<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'metric_date',
    'event_key',
    'locale_id',
    'domain_id',
    'total_count',
    'unique_visitors',
    'metadata',
])]
class AnalyticsDailyMetric extends Model
{
    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'total_count' => 'integer',
            'unique_visitors' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
