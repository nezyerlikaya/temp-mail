<?php

namespace App\Services\Comments;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CommentReplyService
{
    public function __construct(
        private readonly CommentStore $store,
        private readonly CommentSettingsStore $settings,
    ) {}

    /** @param array<string, mixed> $payload */
    public function reply(User $actor, Comment $parent, array $payload, Request $request): Comment
    {
        $settings = $this->settings->settings();

        if (! ($settings['replies_active'] ?? true) || $parent->reply_depth >= 1) {
            throw ValidationException::withMessages(['content' => 'Replies are limited to one level for this release.']);
        }

        $reply = $this->store->create($parent->post, [
            'parent_id' => $parent->id,
            'author_name' => $actor->name,
            'author_email' => $actor->email,
            'content' => $payload['content'],
        ], $request, $actor);

        $reply->forceFill([
            'reply_depth' => 1,
            'status' => 'approved',
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ])->save();

        return $reply->refresh();
    }
}
