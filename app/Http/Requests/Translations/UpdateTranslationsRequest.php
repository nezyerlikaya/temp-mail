<?php

namespace App\Http\Requests\Translations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTranslationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit translations') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', Rule::exists('locales', 'locale')->where(fn ($query) => $query->where('is_active', true)->where('locale', '!=', 'en'))],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.value' => ['nullable', 'string', 'max:10000'],
            'translations.*.status' => ['nullable', Rule::in(['draft', 'translated'])],
        ];
    }
}
