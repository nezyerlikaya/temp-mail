<?php

namespace App\Actions\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Validation\ValidationException;

class AttachPostTaxonomyAction
{
    /** @param array<int, int|string> $tagIds */
    public function handle(BlogPost $post, ?int $categoryId, array $tagIds): void
    {
        if ($categoryId !== null) {
            $category = BlogCategory::query()->find($categoryId);

            if ($category && $category->locale_id !== $post->locale_id) {
                throw ValidationException::withMessages(['blog_category_id' => 'Choose a category from the same language as the post.']);
            }
        }

        $tags = BlogTag::query()->whereIn('id', $tagIds)->get();

        if ($tags->contains(fn (BlogTag $tag): bool => $tag->locale_id !== $post->locale_id)) {
            throw ValidationException::withMessages(['tag_ids' => 'Choose tags from the same language as the post.']);
        }

        $post->tags()->sync($tagIds);
    }
}
