<?php

namespace App\Http\Requests\Localization;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateLocalesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.locale-launch-center.manage') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'action' => ['required', 'in:activate,deactivate'],
            'locales' => ['required', 'array', 'min:1'],
            'locales.*' => ['required', 'string', 'exists:locales,locale'],
        ];
    }
}
