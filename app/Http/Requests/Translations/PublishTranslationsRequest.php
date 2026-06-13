<?php

namespace App\Http\Requests\Translations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublishTranslationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('publish translations') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', Rule::exists('locales', 'locale')->where(fn ($query) => $query->where('is_active', true)->where('locale', '!=', 'en'))],
            'source_ids' => ['required', 'array', 'min:1'],
            'source_ids.*' => ['integer', Rule::exists('translation_sources', 'id')->where('is_active', true)],
        ];
    }
}
