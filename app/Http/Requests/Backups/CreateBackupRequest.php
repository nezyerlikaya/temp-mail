<?php

namespace App\Http\Requests\Backups;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.backups-health.create') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['database', 'storage', 'full'])],
        ];
    }
}
