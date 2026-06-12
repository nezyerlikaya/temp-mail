<?php

namespace App\Http\Requests\Sections;

use Illuminate\Foundation\Http\FormRequest;

class DeleteSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.sections-studio.delete') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'confirm_delete' => ['accepted'],
        ];
    }
}
