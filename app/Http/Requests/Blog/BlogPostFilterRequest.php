<?php

namespace App\Http\Requests\Blog;

use App\Models\BlogCategory;
use App\Models\Locale;
use App\Models\User;
use App\Services\Blog\BlogPostStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogPostFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.blog-studio.view') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'locale_id' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === 'all' || $value === null || $value === '') {
                    return;
                }

                if (! ctype_digit((string) $value) || ! Locale::query()->whereKey((int) $value)->exists()) {
                    $fail('Choose a valid language filter.');
                }
            }],
            'status' => ['nullable', Rule::in(['all', ...array_keys(app(BlogPostStore::class)->statuses())])],
            'category_id' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === 'all' || $value === null || $value === '') {
                    return;
                }

                if (! ctype_digit((string) $value) || ! BlogCategory::query()->whereKey((int) $value)->exists()) {
                    $fail('Choose a valid category filter.');
                }
            }],
            'author_id' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === 'all' || $value === null || $value === '') {
                    return;
                }

                if (! ctype_digit((string) $value) || ! User::query()->whereKey((int) $value)->exists()) {
                    $fail('Choose a valid author filter.');
                }
            }],
            'date' => ['nullable', 'in:all,today,week'],
        ];
    }
}
