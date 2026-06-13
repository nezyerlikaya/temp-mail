<?php

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class DeleteCommentAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handle(User $actor, Comment $comment): void
    {
        $metadata = [
            'comment_id' => $comment->id,
            'post_id' => $comment->blog_post_id,
            'status' => $comment->status,
            'reply_count' => $comment->replies()->count(),
        ];

        $this->audit->record('comment.deleted', $actor, null, $metadata, ['module' => 'content', 'target' => $comment]);
        $comment->delete();
    }
}
