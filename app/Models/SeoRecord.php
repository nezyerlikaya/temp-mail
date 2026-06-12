<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'locale_id',
    'target_type',
    'target_key',
    'targetable_type',
    'targetable_id',
    'meta_title',
    'meta_description',
    'canonical_url',
    'robots_index',
    'robots_follow',
    'include_in_sitemap',
    'sitemap_priority',
    'sitemap_change_frequency',
    'og_title',
    'og_description',
    'og_image_media_id',
    'twitter_card',
    'twitter_title',
    'twitter_description',
    'twitter_image_media_id',
    'schema_type',
    'schema_json',
    'breadcrumb_title',
    'created_by',
    'updated_by',
])]
class SeoRecord extends Model
{
    protected function casts(): array
    {
        return [
            'locale_id' => 'integer',
            'targetable_id' => 'integer',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'include_in_sitemap' => 'boolean',
            'sitemap_priority' => 'decimal:1',
            'og_image_media_id' => 'integer',
            'twitter_image_media_id' => 'integer',
            'schema_json' => 'array',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    /** @return BelongsTo<Locale, $this> */
    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    /** @return BelongsTo<MediaAsset, $this> */
    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'og_image_media_id');
    }

    /** @return BelongsTo<MediaAsset, $this> */
    public function twitterImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'twitter_image_media_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }
}
