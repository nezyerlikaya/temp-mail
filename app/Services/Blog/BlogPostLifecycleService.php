<?php

namespace App\Services\Blog;

use App\Models\BlogPost;
use Illuminate\Validation\ValidationException;

class BlogPostLifecycleService
{
    /** @return array<int, string> */
    public function allowedTransitions(BlogPost $post): array
    {
        return match ($post->status) {
            'draft' => ['published', 'hidden', 'scheduled', 'trashed'],
            'scheduled' => ['published', 'hidden', 'draft', 'trashed'],
            'published' => ['hidden', 'draft', 'trashed'],
            'hidden' => ['draft', 'published', 'scheduled', 'trashed'],
            'trashed' => ['draft'],
            default => ['draft'],
        };
    }

    public function assertCanTransition(BlogPost $post, string $target): void
    {
        if (! in_array($target, $this->allowedTransitions($post), true)) {
            throw ValidationException::withMessages([
                'status' => 'This post cannot move from '.str($post->status)->headline().' to '.str($target)->headline().'.',
            ]);
        }
    }
}
