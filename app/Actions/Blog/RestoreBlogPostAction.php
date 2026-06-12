<?php

namespace App\Actions\Blog;

use App\Models\BlogPost;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Blog\BlogPostLifecycleService;

class RestoreBlogPostAction
{
    public function __construct(
        private readonly BlogPostLifecycleService $lifecycle,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, BlogPost $post): BlogPost
    {
        $this->lifecycle->assertCanTransition($post, 'draft');

        $post->update([
            'status' => 'draft',
            'trashed_at' => null,
        ]);

        $this->audit->record('blog_post.restored', $actor, null, [
            'post_id' => $post->id,
            'slug' => $post->slug,
        ], ['module' => 'blog', 'action' => 'Restore blog post', 'target' => $post]);

        return $post;
    }
}
