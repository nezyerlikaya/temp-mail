<?php

namespace App\Actions\Blog;

use App\Models\BlogPost;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Blog\BlogPostLifecycleService;

class HideBlogPostAction
{
    public function __construct(
        private readonly BlogPostLifecycleService $lifecycle,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, BlogPost $post): BlogPost
    {
        $this->lifecycle->assertCanTransition($post, 'hidden');

        $post->update([
            'status' => 'hidden',
            'trashed_at' => null,
        ]);

        $this->audit->record('blog_post.hidden', $actor, null, [
            'post_id' => $post->id,
            'slug' => $post->slug,
        ], ['module' => 'blog', 'action' => 'Hide blog post', 'target' => $post]);

        return $post;
    }
}
