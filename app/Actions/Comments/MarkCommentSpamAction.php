<?php

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class MarkCommentSpamAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handle(User $actor, Comment $comment, string $status = 'spam'): Comment
    {
        $previousStatus = $comment->status;

        $comment->forceFill([
            'status' => $status,
            'approved_by' => null,
            'approved_at' => null,
            'trashed_at' => $status === 'trashed' ? now() : null,
        ])->save();

        $this->audit->record($status === 'trashed' ? 'comment.trashed' : 'comment.marked_spam', $actor, null, [
            'comment_id' => $comment->id,
            'post_id' => $comment->blog_post_id,
            'previous_status' => $previousStatus,
            'spam_score' => $comment->spam_score,
            'provider_decision' => $comment->provider_decision,
        ], ['module' => 'content', 'target' => $comment]);

        return $comment->refresh();
    }
}
