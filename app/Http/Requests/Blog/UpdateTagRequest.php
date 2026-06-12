<?php

namespace App\Http\Requests\Blog;

use App\Models\BlogTag;
use App\Services\Blog\BlogSlugService;
use App\Services\Blog\BlogTaxonomyService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (blank($this->input('slug')) && filled($this->input('name'))) {
            $this->merge(['slug' => app(BlogSlugService::class)->normalize((string) $this->input('name'))]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can('admin.taxonomy.update') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $tag = $this->route('blogTag');
        $tagId = $tag instanceof BlogTag ? $tag->id : null;
        $localeId = $this->integer('locale_id');

        return [
            'locale_id' => ['required', 'integer', 'exists:locales,id'],
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('blog_tags', 'slug')->where('locale_id', $localeId)->ignore($tagId)],
            'description' => ['nullable', 'string', 'max:600'],
            'status' => ['required', Rule::in(array_keys(app(BlogTaxonomyService::class)->statuses()))],
        ];
    }
}
