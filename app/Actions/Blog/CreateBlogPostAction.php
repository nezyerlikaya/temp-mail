<?php

namespace App\Actions\Blog;

use App\Actions\Media\AttachMediaUsageAction;
use App\Models\BlogPost;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Blog\BlogSlugService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateBlogPostAction
{
    public function __construct(
        private readonly BlogSlugService $slugs,
        private readonly PublishBlogPostAction $publisher,
        private readonly AttachPostTaxonomyAction $taxonomy,
        private readonly AuditLogger $audit,
        private readonly AttachMediaUsageAction $attachMediaUsage,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, array $payload): BlogPost
    {
        return DB::transaction(function () use ($actor, $payload): BlogPost {
            $tagIds = $payload['tag_ids'] ?? [];
            $payload['status'] = $this->publisher->statusForIntent($payload['intent'] ?? null, (string) ($payload['status'] ?? 'draft'));
            unset($payload['tag_ids']);
            unset($payload['intent']);

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
            $this->taxonomy->handle($post, $post->blog_category_id, $tagIds);
            $this->syncMediaUsage($actor, $post);

            $this->audit->record('blog_post.created', $actor, null, [
                'post_id' => $post->id,
                'locale_id' => $post->locale_id,
                'slug' => $post->slug,
                'status' => $post->status,
            ], ['module' => 'blog', 'action' => 'Create blog post', 'target' => $post]);

            $this->publisher->auditTransition($actor, $post, $post->status);

            return $post;
        });
    }

    private function syncMediaUsage(User $actor, BlogPost $post): void
    {
        $asset = $post->featured_media_id ? MediaAsset::query()->find($post->featured_media_id) : null;

        if (! $asset) {
            return;
        }

        $this->attachMediaUsage->handle($actor, $asset, [
            'module' => 'blog',
            'usage_context' => 'blog_studio',
            'slot' => 'featured_media_id',
            'usable_type' => BlogPost::class,
            'usable_id' => (string) $post->id,
            'label' => 'Blog post featured image',
        ]);
    }
}
