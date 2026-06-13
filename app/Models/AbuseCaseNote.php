<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['abuse_report_id', 'author_id', 'body'])]
class AbuseCaseNote extends Model
{
    public function report(): BelongsTo
    {
        return $this->belongsTo(AbuseReport::class, 'abuse_report_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id')->withTrashed();
    }
}
