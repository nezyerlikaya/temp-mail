<?php

namespace App\Actions\Blog;

use App\Models\BlogPost;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class PublishBlogPostAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function statusForIntent(?string $intent, string $fallback): string
    {
        return match ($intent) {
            'publish' => 'published',
            'hide' => 'hidden',
            default => $fallback,
        };
    }

    public function auditTransition(User $actor, BlogPost $post, string $status): void
    {
        if (! in_array($status, ['published', 'hidden'], true)) {
            return;
        }

        $this->audit->record('blog_post.'.$status, $actor, null, [
            'post_id' => $post->id,
            'status' => $status,
        ], [
            'module' => 'blog',
            'action' => $status === 'published' ? 'Publish blog post' : 'Hide blog post',
            'target' => $post,
        ]);
    }
}
