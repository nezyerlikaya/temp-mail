<?php

namespace App\Http\Requests\Updates;

use Illuminate\Foundation\Http\FormRequest;

class InstallUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.update-center.install') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'confirm_backup' => ['accepted'],
            'confirm_protected_paths' => ['accepted'],
            'maintenance_message' => ['nullable', 'string', 'max:160'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'confirm_backup' => 'backup confirmation',
            'confirm_protected_paths' => 'protected path confirmation',
            'maintenance_message' => 'maintenance message',
        ];
    }
}
