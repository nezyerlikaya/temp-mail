<?php

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Services\Comments\CommentStatusService;

class MarkCommentSpamAction
{
    public function __construct(private readonly CommentStatusService $statuses) {}

    public function handle(User $actor, Comment $comment, string $status = 'spam'): Comment
    {
        return $status === 'trashed'
            ? $this->statuses->trash($actor, $comment)
            : $this->statuses->spam($actor, $comment);
    }
}
