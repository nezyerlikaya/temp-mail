<?php

namespace App\Http\Requests\Translations;

use App\Services\Translations\TranslationGroupRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTranslationSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage translation sources') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'group_key' => ['required', 'string', Rule::in(app(TranslationGroupRegistry::class)->keys())],
            'translation_key' => ['required', 'string', 'max:180', 'regex:/^[a-z][a-z0-9]*(\.[a-z][a-z0-9]*)+$/', 'unique:translation_sources,translation_key'],
            'source_value' => ['required', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:1000'],
            'value_type' => ['required', Rule::in(['short_text', 'long_text', 'rich_text', 'boolean'])],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return [
            ...$this->validated(),
            'is_required' => (bool) $this->boolean('is_required'),
            'is_active' => (bool) $this->boolean('is_active', true),
            'sort_order' => (int) $this->validated('sort_order', 100),
        ];
    }
}
