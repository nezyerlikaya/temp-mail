<?php

namespace App\Services\Blog;

use App\Models\BlogPost;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class BlogPostAuditLogger
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $metadata */
    public function record(string $event, User $actor, BlogPost $post, array $metadata = []): void
    {
        $this->audit->record('blog_post.'.$event, $actor, null, [
            'post_id' => $post->id,
            'slug' => $post->slug,
            ...$metadata,
        ], [
            'module' => 'blog',
            'action' => str('blog post '.$event)->headline()->toString(),
            'target' => $post,
        ]);
    }
}
