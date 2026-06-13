<?php

namespace App\Actions\Comments;

use App\Models\BlogPost;
use App\Models\Comment;
use App\Models\User;
use App\Services\Analytics\AnalyticsEventTracker;
use App\Services\Comments\CommentSettingsStore;
use App\Services\Comments\CommentStore;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\Request;

class SubmitCommentAction
{
    public function __construct(
        private readonly CommentStore $store,
        private readonly NotificationService $notifications,
        private readonly AnalyticsEventTracker $analytics,
        private readonly CommentSettingsStore $settings,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(BlogPost $post, array $payload, Request $request, ?User $user = null): Comment
    {
        $comment = $this->store->create($post, $payload, $request, $user);

        $this->analytics->trackSafely('comment.submitted', [
            'user' => $user,
            'locale_id' => $post->locale_id,
            'request' => $request,
            'metadata' => [
                'comment_status' => $comment->status,
                'post_id' => $post->id,
            ],
        ]);

        if ($comment->status === 'pending' && ($this->settings->settings()['notify_pending_admins'] ?? true)) {
            $this->notifications->dispatch([
                'event_key' => 'new_pending_comment',
                'type' => 'content',
                'severity' => 'info',
                'title' => 'New pending comment',
                'message' => 'A new blog comment is waiting for review.',
                'related_module' => 'content',
                'target_type' => Comment::class,
                'target_id' => $comment->id,
                'action_route' => 'admin.comment-moderation.index',
                'action_parameters' => ['status' => 'pending'],
            ], sendEmail: false);
        }

        if ($comment->status === 'spam') {
            $this->notifications->dispatch([
                'event_key' => 'spam_comment_detected',
                'type' => 'content',
                'severity' => 'warning',
                'title' => 'Spam comment detected',
                'message' => 'A suspicious blog comment was marked as spam.',
                'related_module' => 'content',
                'target_type' => Comment::class,
                'target_id' => $comment->id,
                'action_route' => 'admin.comment-moderation.index',
                'action_parameters' => ['status' => 'spam'],
            ], sendEmail: false);
        }

        return $comment;
    }
}
