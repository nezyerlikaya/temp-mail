<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['comment_id', 'edited_by', 'previous_excerpt', 'new_excerpt'])]
class CommentEditHistory extends Model
{
    protected function casts(): array
    {
        return ['comment_id' => 'integer', 'edited_by' => 'integer'];
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by')->withTrashed();
    }
}
