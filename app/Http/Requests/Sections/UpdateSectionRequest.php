<?php

namespace App\Http\Requests\Sections;

class UpdateSectionRequest extends StoreSectionRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.sections-studio.update') ?? false;
    }
}
