<?php

namespace App\Actions\Blog;

use App\Models\BlogPost;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Blog\BlogPostLifecycleService;

class TrashBlogPostAction
{
    public function __construct(
        private readonly BlogPostLifecycleService $lifecycle,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, BlogPost $post): BlogPost
    {
        $this->lifecycle->assertCanTransition($post, 'trashed');

        $post->update([
            'status' => 'trashed',
            'trashed_at' => now(),
        ]);

        $this->audit->record('blog_post.trashed', $actor, null, [
            'post_id' => $post->id,
            'slug' => $post->slug,
        ], ['module' => 'blog', 'action' => 'Trash blog post', 'target' => $post]);

        return $post;
    }
}
