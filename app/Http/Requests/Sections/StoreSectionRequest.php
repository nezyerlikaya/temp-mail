<?php

namespace App\Http\Requests\Sections;

use App\Services\Sections\SectionPlacementRegistry;
use App\Services\Sections\SectionStore;
use App\Services\Sections\SectionTypeRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSectionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (blank($this->input('device_visibility'))) {
            $this->merge(['device_visibility' => 'all']);
        }
    }

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
            'settings.button_label' => ['nullable', 'string', 'max:120'],
            'settings.button_url' => ['nullable', 'string', 'max:500'],
            'settings.post_count' => ['nullable', 'integer', 'min:1', 'max:24'],
            'settings.category_id' => ['nullable', 'integer', 'exists:blog_categories,id'],
            'settings.layout' => ['nullable', Rule::in(['grid', 'list', 'compact'])],
            'status' => ['required', Rule::in(array_keys(app(SectionStore::class)->editorStatuses()))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'visibility' => ['required', Rule::in(array_keys(app(SectionStore::class)->visibilities()))],
            'device_visibility' => ['required', Rule::in(array_keys(app(SectionStore::class)->deviceVisibilities()))],
        ];
    }
}
