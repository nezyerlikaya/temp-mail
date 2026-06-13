<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['abuse_report_id', 'media_asset_id', 'label', 'is_sensitive', 'private_disk', 'private_path', 'added_by'])]
class AbuseEvidence extends Model
{
    protected $table = 'abuse_evidences';

    protected function casts(): array
    {
        return ['is_sensitive' => 'boolean'];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(AbuseReport::class, 'abuse_report_id');
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withTrashed();
    }
}
