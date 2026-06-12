<?php

namespace App\Actions\Blog;

use App\Models\BlogTag;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Blog\BlogSlugService;

class UpdateTagAction
{
    public function __construct(
        private readonly BlogSlugService $slugs,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, BlogTag $tag, array $payload): BlogTag
    {
        $payload['slug'] = $payload['slug'] ?: $this->slugs->normalize((string) $payload['name']);
        $tag->update($payload);

        $this->audit->record('blog_tag.updated', $actor, null, [
            'tag_id' => $tag->id,
            'locale_id' => $tag->locale_id,
            'slug' => $tag->slug,
        ], ['module' => 'blog', 'action' => 'Update blog tag', 'target' => $tag]);

        return $tag->refresh();
    }
}
