<?php

namespace App\Actions\Blog;

use App\Models\BlogCategory;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Blog\BlogSlugService;

class CreateCategoryAction
{
    public function __construct(
        private readonly BlogSlugService $slugs,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, array $payload): BlogCategory
    {
        $payload['slug'] = $payload['slug'] ?: $this->slugs->normalize((string) $payload['name']);
        $payload['is_active'] = ($payload['status'] ?? 'active') === 'active';
        $payload['sort_order'] = $payload['sort_order'] ?? 0;

        $category = BlogCategory::query()->create($payload);

        $this->audit->record('blog_category.created', $actor, null, [
            'category_id' => $category->id,
            'locale_id' => $category->locale_id,
            'slug' => $category->slug,
        ], ['module' => 'blog', 'action' => 'Create blog category', 'target' => $category]);

        return $category;
    }
}
