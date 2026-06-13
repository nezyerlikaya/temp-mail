<?php

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Services\Comments\CommentStatusService;

class RestoreSpamFalsePositiveAction
{
    public function __construct(private readonly CommentStatusService $status) {}

    public function handle(User $actor, Comment $comment, string $status): Comment
    {
        $comment->forceFill([
            'original_provider_decision' => $comment->original_provider_decision ?: $comment->provider_decision,
        ])->save();

        return $status === 'approved'
            ? $this->status->approve($actor, $comment, 'false_positive')
            : $this->status->pending($actor, $comment, 'false_positive');
    }
}
