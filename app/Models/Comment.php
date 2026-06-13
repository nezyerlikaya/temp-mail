<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'blog_post_id',
    'parent_id',
    'user_id',
    'locale_id',
    'author_name',
    'author_email',
    'author_email_hash',
    'ip_hash',
    'user_agent_metadata',
    'content',
    'content_excerpt',
    'status',
    'spam_score',
    'spam_provider',
    'provider_decision',
    'approved_by',
    'approved_at',
    'trashed_at',
])]
class Comment extends Model
{
    protected function casts(): array
    {
        return [
            'user_agent_metadata' => 'array',
            'spam_score' => 'integer',
            'approved_at' => 'datetime',
            'trashed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<BlogPost, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    /** @return BelongsTo<Comment, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /** @return HasMany<Comment, $this> */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Locale, $this> */
    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
