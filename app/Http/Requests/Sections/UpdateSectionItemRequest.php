<?php

namespace App\Http\Requests\Sections;

use App\Models\Section;
use App\Models\SectionItem;
use Illuminate\Validation\Rule;

class UpdateSectionItemRequest extends StoreSectionItemRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $section = $this->route('section');
        $item = $this->route('sectionItem');
        $sectionId = $section instanceof Section ? $section->id : null;
        $itemId = $item instanceof SectionItem ? $item->id : null;

        return [
            'title' => [
                'required',
                'string',
                'max:240',
                Rule::unique('section_items', 'title')
                    ->where(fn ($query) => $query->where('section_id', $sectionId)->where('status', '!=', 'removed'))
                    ->ignore($itemId),
            ],
            'content' => ['required', 'string', 'max:10000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
