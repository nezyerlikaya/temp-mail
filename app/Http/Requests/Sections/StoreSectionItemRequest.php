<?php

namespace App\Http\Requests\Sections;

use App\Models\Section;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSectionItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.sections-studio.items.update') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $section = $this->route('section');
        $sectionId = $section instanceof Section ? $section->id : null;

        return [
            'title' => [
                'required',
                'string',
                'max:240',
                Rule::unique('section_items', 'title')->where(
                    fn ($query) => $query->where('section_id', $sectionId)->where('status', '!=', 'removed')
                ),
            ],
            'content' => ['required', 'string', 'max:10000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
