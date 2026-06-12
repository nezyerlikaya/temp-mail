<?php

namespace App\Models;

use Database\Factories\SectionItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'section_id',
    'title',
    'content',
    'status',
    'sort_order',
])]
class SectionItem extends Model
{
    /** @use HasFactory<SectionItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'section_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<Section, $this> */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
