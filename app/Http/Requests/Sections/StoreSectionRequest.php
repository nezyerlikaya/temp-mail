<?php

namespace App\Http\Requests\Sections;

use App\Services\Sections\SectionPlacementRegistry;
use App\Services\Sections\SectionStore;
use App\Services\Sections\SectionTypeRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.sections-studio.create') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'locale_id' => ['required', 'integer', 'exists:locales,id'],
            'section_type' => ['required', Rule::in(app(SectionTypeRegistry::class)->keys())],
            'placement' => ['required', Rule::in(app(SectionPlacementRegistry::class)->keys())],
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:600'],
            'content' => ['nullable', 'string', 'max:10000'],
            'settings' => ['nullable', 'array'],
            'status' => ['required', Rule::in(array_keys(app(SectionStore::class)->editorStatuses()))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'visibility' => ['required', Rule::in(array_keys(app(SectionStore::class)->visibilities()))],
        ];
    }
}
