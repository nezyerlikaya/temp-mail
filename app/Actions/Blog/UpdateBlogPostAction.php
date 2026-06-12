<?php

namespace App\Actions\Blog;

use App\Models\BlogPost;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Blog\BlogSlugService;
use Illuminate\Support\Facades\DB;

class UpdateBlogPostAction
{
    public function __construct(
        private readonly BlogSlugService $slugs,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, BlogPost $post, array $payload): BlogPost
    {
        return DB::transaction(function () use ($actor, $post, $payload): BlogPost {
            $tagIds = $payload['tag_ids'] ?? null;
            unset($payload['tag_ids']);

            $payload['slug'] = $payload['slug'] ?: $this->slugs->fromTitle((string) $payload['title']);
            $payload['published_at'] = ($payload['status'] ?? $post->status) === 'published'
                ? ($payload['published_at'] ?? $post->published_at ?? now())
                : ($payload['published_at'] ?? null);
            $payload['trashed_at'] = ($payload['status'] ?? $post->status) === 'trashed'
                ? ($payload['trashed_at'] ?? $post->trashed_at ?? now())
                : null;

            $post->update($payload);

            if (is_array($tagIds)) {
                $post->tags()->sync($tagIds);
            }

            $this->audit->record('blog_post.updated', $actor, null, [
                'post_id' => $post->id,
                'locale_id' => $post->locale_id,
                'slug' => $post->slug,
                'status' => $post->status,
            ], ['module' => 'blog', 'action' => 'Update blog post', 'target' => $post]);

            return $post->refresh();
        });
    }
}
