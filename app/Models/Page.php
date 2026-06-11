<?php

namespace App\Models;

use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'locale_id',
    'title',
    'slug',
    'excerpt',
    'content',
    'content_readiness',
    'featured_media_id',
    'page_type',
    'status',
    'author_id',
    'published_at',
    'trashed_at',
])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'locale_id' => 'integer',
            'featured_media_id' => 'integer',
            'author_id' => 'integer',
            'published_at' => 'datetime',
            'trashed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Locale, $this> */
    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id')->withTrashed();
    }

    /** @return BelongsTo<MediaAsset, $this> */
    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'featured_media_id');
    }
}
