<?php

namespace App\Http\Requests\Translations;

use App\Services\Translations\TranslationGroupRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslationEditorFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view translations') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'locale' => ['nullable', 'string', 'max:20', Rule::exists('locales', 'locale')->where(fn ($query) => $query->where('is_active', true)->where('locale', '!=', 'en'))],
            'mode' => ['nullable', Rule::in(['registry', 'editor'])],
            'q' => ['nullable', 'string', 'max:120'],
            'group' => ['nullable', Rule::in(['all', ...app(TranslationGroupRegistry::class)->keys()])],
            'status' => ['nullable', Rule::in(['all', 'missing', 'draft', 'translated', 'reviewed', 'published'])],
            'requirement' => ['nullable', Rule::in(['all', 'required', 'optional'])],
            'state' => ['nullable', Rule::in(['all', 'active', 'passive'])],
            'missing' => ['nullable', Rule::in(['all', 'missing'])],
            'per_page' => ['nullable', 'integer', 'min:6', 'max:50'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        return [
            'locale' => (string) $this->validated('locale', ''),
            'mode' => (string) $this->validated('mode', 'registry'),
            'q' => (string) $this->validated('q', ''),
            'group' => (string) $this->validated('group', 'all'),
            'status' => (string) $this->validated('status', 'all'),
            'requirement' => (string) $this->validated('requirement', 'all'),
            'state' => (string) $this->validated('state', 'all'),
            'missing' => (string) $this->validated('missing', 'all'),
            'per_page' => (int) $this->validated('per_page', 12),
        ];
    }
}
