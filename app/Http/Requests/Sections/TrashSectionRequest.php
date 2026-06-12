<?php

namespace App\Http\Requests\Sections;

use Illuminate\Foundation\Http\FormRequest;

class TrashSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.sections-studio.trash') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'confirm_trash' => ['accepted'],
        ];
    }
}
