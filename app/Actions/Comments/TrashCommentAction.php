<?php

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Services\Comments\CommentStatusService;

class TrashCommentAction
{
    public function __construct(private readonly CommentStatusService $status) {}

    public function handle(User $actor, Comment $comment): Comment
    {
        return $this->status->trash($actor, $comment);
    }
}
