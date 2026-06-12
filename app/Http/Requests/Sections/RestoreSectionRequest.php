<?php

namespace App\Http\Requests\Sections;

use Illuminate\Foundation\Http\FormRequest;

class RestoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.sections-studio.restore') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [];
    }
}
