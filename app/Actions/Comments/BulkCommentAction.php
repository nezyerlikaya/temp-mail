<?php

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Services\Comments\CommentStatusService;

class BulkCommentAction
{
    public function __construct(private readonly CommentStatusService $status) {}

    /** @param array<int, int> $ids */
    public function handle(User $actor, string $action, array $ids): int
    {
        $comments = Comment::query()->whereIn('id', $ids)->get();

        $comments->each(function (Comment $comment) use ($actor, $action): void {
            match ($action) {
                'approve' => $this->status->approve($actor, $comment),
                'spam' => $this->status->spam($actor, $comment),
                'trash' => $this->status->trash($actor, $comment),
                'restore' => $this->status->restore($actor, $comment),
            };
        });

        return $comments->count();
    }
}
