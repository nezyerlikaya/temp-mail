<?php

namespace App\Services\Blog;

use App\Models\BlogPost;

class BlogPostOwnershipService
{
    /** @return array{author_id: int|null, can_transfer: bool, message: string} */
    public function readiness(BlogPost $post): array
    {
        return [
            'author_id' => $post->author_id,
            'can_transfer' => $post->author_id !== null,
            'message' => $post->author_id === null
                ? 'This post is system-owned and ready for future reassignment rules.'
                : 'Author ownership is recorded for future deletion or suspension transfer workflows.',
        ];
    }
}
