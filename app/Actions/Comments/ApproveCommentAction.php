<?php

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class ApproveCommentAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handle(User $actor, Comment $comment): Comment
    {
        $previousStatus = $comment->status;

        $comment->forceFill([
            'status' => 'approved',
            'approved_by' => $actor->id,
            'approved_at' => now(),
            'trashed_at' => null,
        ])->save();

        $this->audit->record('comment.approved', $actor, null, [
            'comment_id' => $comment->id,
            'post_id' => $comment->blog_post_id,
            'previous_status' => $previousStatus,
            'spam_score' => $comment->spam_score,
        ], ['module' => 'content', 'target' => $comment]);

        return $comment->refresh();
    }
}
