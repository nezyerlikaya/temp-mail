<?php

namespace App\Actions\Blog;

use App\Models\BlogTag;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Blog\BlogSlugService;

class CreateTagAction
{
    public function __construct(
        private readonly BlogSlugService $slugs,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, array $payload): BlogTag
    {
        $payload['slug'] = $payload['slug'] ?: $this->slugs->normalize((string) $payload['name']);

        $tag = BlogTag::query()->create($payload);

        $this->audit->record('blog_tag.created', $actor, null, [
            'tag_id' => $tag->id,
            'locale_id' => $tag->locale_id,
            'slug' => $tag->slug,
        ], ['module' => 'blog', 'action' => 'Create blog tag', 'target' => $tag]);

        return $tag;
    }
}
