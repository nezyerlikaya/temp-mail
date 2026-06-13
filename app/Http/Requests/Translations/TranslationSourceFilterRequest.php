<?php

namespace App\Http\Requests\Translations;

use App\Services\Translations\TranslationGroupRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslationSourceFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view translations') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'group' => ['nullable', 'string', Rule::in(['all', ...app(TranslationGroupRegistry::class)->keys()])],
            'requirement' => ['nullable', 'string', Rule::in(['all', 'required', 'optional'])],
            'state' => ['nullable', 'string', Rule::in(['all', 'active', 'passive'])],
            'missing' => ['nullable', 'string', Rule::in(['all', 'missing'])],
            'per_page' => ['nullable', 'integer', 'min:6', 'max:50'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        return [
            'q' => (string) $this->validated('q', ''),
            'group' => (string) $this->validated('group', 'all'),
            'requirement' => (string) $this->validated('requirement', 'all'),
            'state' => (string) $this->validated('state', 'all'),
            'missing' => (string) $this->validated('missing', 'all'),
            'per_page' => (int) $this->validated('per_page', 12),
        ];
    }
}
