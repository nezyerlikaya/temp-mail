<?php

namespace App\Http\Requests\Blog;

use App\Services\Blog\BlogPostStore;
use App\Services\Blog\BlogSlugService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlogPostRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (blank($this->input('slug')) && filled($this->input('title'))) {
            $this->merge(['slug' => app(BlogSlugService::class)->fromTitle((string) $this->input('title'))]);
        }
    }

    public function authorize(): bool
    {
        if (! ($this->user()?->can('admin.blog-studio.create') ?? false)) {
            return false;
        }

        if ($this->hasTaxonomyInput() && ! ($this->user()?->can('admin.taxonomy.attach') ?? false)) {
            return false;
        }

        return match ($this->input('intent')) {
            'publish' => $this->user()?->can('admin.blog-studio.publish') ?? false,
            'hide' => $this->user()?->can('admin.blog-studio.hide') ?? false,
            default => true,
        };
    }

    private function hasTaxonomyInput(): bool
    {
        return filled($this->input('blog_category_id')) || filled($this->input('tag_ids'));
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $store = app(BlogPostStore::class);
        $localeId = $this->integer('locale_id');

        return [
            'locale_id' => ['required', 'integer', 'exists:locales,id'],
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('blog_posts', 'slug')->where('locale_id', $localeId),
            ],
            'excerpt' => ['nullable', 'string', 'max:600'],
            'content' => ['nullable', 'string', 'max:30000'],
            'content_readiness' => ['required', Rule::in(array_keys($store->contentReadinessOptions()))],
            'featured_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'blog_category_id' => ['nullable', 'integer', Rule::exists('blog_categories', 'id')->where('locale_id', $localeId)],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', Rule::exists('blog_tags', 'id')->where('locale_id', $localeId)],
            'status' => ['required', Rule::in(array_keys($store->editorStatuses()))],
            'intent' => ['nullable', Rule::in(['save_draft', 'publish', 'hide'])],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
