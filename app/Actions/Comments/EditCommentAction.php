<?php

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\CommentEditHistory;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Comments\CommentContentSanitizer;
use Illuminate\Support\Str;

class EditCommentAction
{
    public function __construct(
        private readonly CommentContentSanitizer $sanitizer,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, Comment $comment, array $payload): Comment
    {
        $previousExcerpt = $comment->content_excerpt;
        $content = $this->sanitizer->sanitize((string) $payload['content']);
        $newExcerpt = Str::limit(strip_tags($content), 180, '');

        CommentEditHistory::query()->create([
            'comment_id' => $comment->id,
            'edited_by' => $actor->id,
            'previous_excerpt' => $previousExcerpt,
            'new_excerpt' => $newExcerpt,
        ]);

        $comment->forceFill([
            'content' => $content,
            'content_excerpt' => $newExcerpt,
            'edited_by' => $actor->id,
            'edited_at' => now(),
        ])->save();

        $this->audit->record('comment.edited', $actor, null, [
            'comment_id' => $comment->id,
            'post_id' => $comment->blog_post_id,
            'previous_excerpt' => $previousExcerpt,
            'new_excerpt' => $newExcerpt,
        ], ['module' => 'content', 'target' => $comment]);

        return $comment->refresh();
    }
}
