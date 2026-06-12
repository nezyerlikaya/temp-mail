<?php

namespace App\Actions\Blog;

use App\Models\BlogPost;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Blog\BlogSlugService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateBlogPostAction
{
    public function __construct(
        private readonly BlogSlugService $slugs,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, array $payload): BlogPost
    {
        return DB::transaction(function () use ($actor, $payload): BlogPost {
            $tagIds = $payload['tag_ids'] ?? [];
            unset($payload['tag_ids']);

            $payload['slug'] = $payload['slug'] ?: $this->slugs->fromTitle((string) $payload['title']);
            $payload['author_id'] = $payload['author_id'] ?? $actor->id;
            $payload['published_at'] = $payload['status'] === 'published'
                ? ($payload['published_at'] ?? now())
                : ($payload['published_at'] ?? null);
            $payload['trashed_at'] = $payload['status'] === 'trashed'
                ? ($payload['trashed_at'] ?? now())
                : null;
            $payload['preview_token'] = $payload['preview_token'] ?? Str::random(48);

            $post = BlogPost::query()->create($payload);
            $post->tags()->sync($tagIds);

            $this->audit->record('blog_post.created', $actor, null, [
                'post_id' => $post->id,
                'locale_id' => $post->locale_id,
                'slug' => $post->slug,
                'status' => $post->status,
            ], ['module' => 'blog', 'action' => 'Create blog post', 'target' => $post]);

            return $post;
        });
    }
}
