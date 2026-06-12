<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'locale_id',
    'template_key',
    'subject',
    'preheader',
    'html_body',
    'plain_text_body',
    'status',
    'updated_by',
])]
class EmailTemplate extends Model
{
    protected function casts(): array
    {
        return [
            'locale_id' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    /** @return BelongsTo<Locale, $this> */
    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }
}
