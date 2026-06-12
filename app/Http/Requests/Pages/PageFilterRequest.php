<?php

namespace App\Http\Requests\Pages;

use App\Models\Locale;
use App\Models\User;
use App\Services\Pages\PageStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.page-studio.view') ?? false;
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
            'page_type' => ['nullable', 'string', 'max:48'],
            'status' => ['nullable', Rule::in(['all', ...array_keys(app(PageStore::class)->statuses())])],
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
