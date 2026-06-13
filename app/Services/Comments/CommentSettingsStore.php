<?php

namespace App\Services\Comments;

use App\Models\BlogPost;
use App\Models\User;
use App\Services\Settings\SettingsStore;

class CommentSettingsStore
{
    public function __construct(private readonly SettingsStore $store) {}

    /** @return array<string, mixed> */
    public function settings(): array
    {
        return array_replace($this->defaults(), $this->store->group('comments'));
    }

    /** @param array<string, mixed> $payload */
    public function update(array $payload, User $actor): array
    {
        $settings = [
            ...$this->settings(),
            ...$payload,
            'maximum_reply_depth' => 1,
        ];

        $this->store->put('comments', $settings, $actor);

        return $settings;
    }

    /** @return array<string, mixed> */
    public function defaults(): array
    {
        return [
            'comments_active' => true,
            'guest_comments_allowed' => true,
            'approval_required' => true,
            'verified_email_required' => false,
            'auto_close_days' => 0,
            'replies_active' => true,
            'maximum_reply_depth' => 1,
            'minimum_length' => 3,
            'maximum_length' => 3000,
            'maximum_links' => 3,
            'blocked_words' => [],
            'notify_pending_admins' => true,
        ];
    }

    public function acceptsComments(BlogPost $post): bool
    {
        $settings = $this->settings();

        if (! ($settings['comments_active'] ?? true) || ! $post->comments_enabled) {
            return false;
        }

        if ($post->comments_closed_at !== null && $post->comments_closed_at->isPast()) {
            return false;
        }

        $days = (int) ($settings['auto_close_days'] ?? 0);

        return $days <= 0 || ! $post->created_at?->lt(now()->subDays($days));
    }
}
