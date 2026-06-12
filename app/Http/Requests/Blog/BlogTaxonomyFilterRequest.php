<?php

namespace App\Http\Requests\Blog;

use App\Models\Locale;
use App\Services\Blog\BlogTaxonomyService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogTaxonomyFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.taxonomy.view') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'tab' => ['nullable', 'in:categories,tags'],
            'q' => ['nullable', 'string', 'max:120'],
            'locale_id' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === 'all' || $value === null || $value === '') {
                    return;
                }

                if (! ctype_digit((string) $value) || ! Locale::query()->whereKey((int) $value)->exists()) {
                    $fail('Choose a valid language filter.');
                }
            }],
            'status' => ['nullable', Rule::in(['all', ...array_keys(app(BlogTaxonomyService::class)->statuses())])],
            'edit_category' => ['nullable', 'integer', 'exists:blog_categories,id'],
            'edit_tag' => ['nullable', 'integer', 'exists:blog_tags,id'],
        ];
    }
}
