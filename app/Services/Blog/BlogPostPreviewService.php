<?php

namespace App\Services\Blog;

use App\Models\BlogPost;
use Illuminate\Support\Facades\URL;

class BlogPostPreviewService
{
    public function previewUrl(BlogPost $post): string
    {
        return URL::temporarySignedRoute('admin.blog-studio.preview', now()->addMinutes(30), $post);
    }

    public function publicUrl(BlogPost $post): string
    {
        $locale = $post->locale?->locale ?? 'en';

        return url('/'.$locale.'/blog/'.$post->slug);
    }

    /** @return array<string, mixed> */
    public function readiness(BlogPost $post): array
    {
        return [
            'preview_url' => $this->previewUrl($post),
            'public_url' => $this->publicUrl($post),
            'signed' => true,
            'expires_in' => '30 minutes',
            'public_ready' => $post->status === 'published',
            'scheduled_ready' => $post->status === 'scheduled' || ($post->published_at !== null && $post->published_at->isFuture()),
        ];
    }
}
