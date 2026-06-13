<?php

namespace App\Services\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class CommentStatusService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function approve(User $actor, Comment $comment, ?string $override = null): Comment
    {
        $previous = $comment->status;

        $comment->forceFill([
            'status' => 'approved',
            'approved_by' => $actor->id,
            'approved_at' => now(),
            'trashed_at' => null,
            'manual_override' => $override,
        ])->save();

        $this->audit->record($override === 'false_positive' ? 'comment.false_positive_restored' : 'comment.approved', $actor, null, [
            'comment_id' => $comment->id,
            'post_id' => $comment->blog_post_id,
            'previous_status' => $previous,
            'provider_decision' => $comment->provider_decision,
            'original_provider_decision' => $comment->original_provider_decision,
        ], ['module' => 'content', 'target' => $comment]);

        return $comment->refresh();
    }

    public function pending(User $actor, Comment $comment, ?string $override = null): Comment
    {
        $previous = $comment->status;

        $comment->forceFill([
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'trashed_at' => null,
            'manual_override' => $override,
        ])->save();

        $this->audit->record($override === 'false_positive' ? 'comment.false_positive_restored' : 'comment.pending', $actor, null, [
            'comment_id' => $comment->id,
            'post_id' => $comment->blog_post_id,
            'previous_status' => $previous,
            'provider_decision' => $comment->provider_decision,
            'original_provider_decision' => $comment->original_provider_decision,
        ], ['module' => 'content', 'target' => $comment]);

        return $comment->refresh();
    }

    public function spam(User $actor, Comment $comment): Comment
    {
        $previous = $comment->status;

        $comment->forceFill([
            'status' => 'spam',
            'approved_by' => null,
            'approved_at' => null,
            'trashed_at' => null,
            'original_provider_decision' => $comment->original_provider_decision ?: $comment->provider_decision,
            'manual_override' => $comment->provider_decision === 'spam' ? null : 'manual_spam',
        ])->save();

        $this->audit->record('comment.marked_spam', $actor, null, [
            'comment_id' => $comment->id,
            'post_id' => $comment->blog_post_id,
            'previous_status' => $previous,
            'spam_score' => $comment->spam_score,
            'provider_decision' => $comment->provider_decision,
        ], ['module' => 'content', 'target' => $comment]);

        return $comment->refresh();
    }

    public function trash(User $actor, Comment $comment): Comment
    {
        $previous = $comment->status;

        $comment->forceFill([
            'status' => 'trashed',
            'approved_by' => null,
            'approved_at' => null,
            'trashed_at' => now(),
        ])->save();

        $this->audit->record('comment.trashed', $actor, null, [
            'comment_id' => $comment->id,
            'post_id' => $comment->blog_post_id,
            'previous_status' => $previous,
        ], ['module' => 'content', 'target' => $comment]);

        return $comment->refresh();
    }

    public function restore(User $actor, Comment $comment): Comment
    {
        $previous = $comment->status;
        $next = $comment->approved_at ? 'approved' : 'pending';

        $comment->forceFill([
            'status' => $next,
            'trashed_at' => null,
        ])->save();

        $this->audit->record('comment.restored', $actor, null, [
            'comment_id' => $comment->id,
            'post_id' => $comment->blog_post_id,
            'previous_status' => $previous,
            'restored_status' => $next,
        ], ['module' => 'content', 'target' => $comment]);

        return $comment->refresh();
    }
}
