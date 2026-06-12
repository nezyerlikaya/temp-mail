<?php

namespace App\Actions\Blog;

use App\Models\BlogPost;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Blog\BlogPostLifecycleService;

class PublishBlogPostAction
{
    public function __construct(
        private readonly BlogPostLifecycleService $lifecycle,
        private readonly AuditLogger $audit,
    ) {}

    public function statusForIntent(?string $intent, string $fallback): string
    {
        return match ($intent) {
            'publish' => 'published',
            'hide' => 'hidden',
            'save_draft' => 'draft',
            default => $fallback,
        };
    }

    public function handle(User $actor, BlogPost $post): BlogPost
    {
        $this->lifecycle->assertCanTransition($post, 'published');

        $post->update([
            'status' => 'published',
            'published_at' => $post->published_at ?? now(),
            'trashed_at' => null,
        ]);

        $this->audit->record('blog_post.published', $actor, null, [
            'post_id' => $post->id,
            'slug' => $post->slug,
        ], ['module' => 'blog', 'action' => 'Publish blog post', 'target' => $post]);

        return $post;
    }

    public function auditTransition(User $actor, BlogPost $post, string $status): void
    {
        if (! in_array($status, ['published', 'hidden'], true)) {
            return;
        }

        $this->audit->record('blog_post.'.$status, $actor, null, [
            'post_id' => $post->id,
            'slug' => $post->slug,
            'status' => $status,
        ], [
            'module' => 'blog',
            'action' => $status === 'published' ? 'Publish blog post' : 'Hide blog post',
            'target' => $post,
        ]);
    }
}
