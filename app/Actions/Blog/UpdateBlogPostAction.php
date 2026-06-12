<?php

namespace App\Actions\Blog;

use App\Actions\Media\AttachMediaUsageAction;
use App\Actions\Media\DetachMediaUsageAction;
use App\Models\BlogPost;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Blog\BlogSlugService;
use Illuminate\Support\Facades\DB;

class UpdateBlogPostAction
{
    public function __construct(
        private readonly BlogSlugService $slugs,
        private readonly PublishBlogPostAction $publisher,
        private readonly AttachPostTaxonomyAction $taxonomy,
        private readonly AuditLogger $audit,
        private readonly AttachMediaUsageAction $attachMediaUsage,
        private readonly DetachMediaUsageAction $detachMediaUsage,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, BlogPost $post, array $payload): BlogPost
    {
        return DB::transaction(function () use ($actor, $post, $payload): BlogPost {
            $tagIds = $payload['tag_ids'] ?? null;
            $previousStatus = $post->status;
            $previousMediaId = $post->featured_media_id;
            $payload['status'] = $this->publisher->statusForIntent($payload['intent'] ?? null, (string) ($payload['status'] ?? $post->status));
            unset($payload['tag_ids']);
            unset($payload['intent']);

            $payload['slug'] = $payload['slug'] ?: $this->slugs->fromTitle((string) $payload['title']);
            $payload['published_at'] = ($payload['status'] ?? $post->status) === 'published'
                ? ($payload['published_at'] ?? $post->published_at ?? now())
                : ($payload['published_at'] ?? null);
            $payload['trashed_at'] = ($payload['status'] ?? $post->status) === 'trashed'
                ? ($payload['trashed_at'] ?? $post->trashed_at ?? now())
                : null;

            $post->update($payload);
            $post->refresh();

            if (is_array($tagIds)) {
                $this->taxonomy->handle($post, $post->blog_category_id, $tagIds);
            }

            $this->syncMediaUsage($actor, $post, $previousMediaId);

            $this->audit->record('blog_post.updated', $actor, null, [
                'post_id' => $post->id,
                'locale_id' => $post->locale_id,
                'slug' => $post->slug,
                'status' => $post->status,
            ], ['module' => 'blog', 'action' => 'Update blog post', 'target' => $post]);

            if ($previousStatus !== $post->status) {
                $this->publisher->auditTransition($actor, $post, $post->status);
            }

            return $post->refresh();
        });
    }

    private function syncMediaUsage(User $actor, BlogPost $post, ?int $previousMediaId): void
    {
        if ($previousMediaId && $previousMediaId !== $post->featured_media_id) {
            $this->detachMediaUsage->handle($actor, [
                'media_asset_id' => $previousMediaId,
                'module' => 'blog',
                'usage_context' => 'blog_studio',
                'slot' => 'featured_media_id',
                'usable_type' => BlogPost::class,
                'usable_id' => (string) $post->id,
            ]);
        }

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
