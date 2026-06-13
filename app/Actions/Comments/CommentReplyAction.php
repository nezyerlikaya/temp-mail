<?php

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Comments\CommentReplyService;
use Illuminate\Http\Request;

class CommentReplyAction
{
    public function __construct(
        private readonly CommentReplyService $replies,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, Comment $parent, array $payload, Request $request): Comment
    {
        $reply = $this->replies->reply($actor, $parent, $payload, $request);

        $this->audit->record('comment.replied_to', $actor, null, [
            'comment_id' => $parent->id,
            'reply_id' => $reply->id,
            'post_id' => $parent->blog_post_id,
        ], ['module' => 'content', 'target' => $parent]);

        return $reply;
    }
}
