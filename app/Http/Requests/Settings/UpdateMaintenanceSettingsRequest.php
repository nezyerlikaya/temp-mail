<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaintenanceSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.settings.manage') === true;
    }

    public function rules(): array
    {
        return [
            'enabled' => ['nullable', 'boolean'],
            'message' => [Rule::requiredIf($this->boolean('enabled')), 'nullable', 'string', 'max:1000'],
            'allowed_admin_ips' => ['nullable', 'array', 'max:25'],
            'allowed_admin_ips.*' => ['required', 'ip'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $ips = preg_split('/[\r\n,]+/', (string) $this->input('allowed_admin_ips'), -1, PREG_SPLIT_NO_EMPTY);

        $this->merge([
            'enabled' => $this->boolean('enabled'),
            'allowed_admin_ips' => array_values(array_unique(array_map('trim', $ips ?: []))),
        ]);
    }
}
