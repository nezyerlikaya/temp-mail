<?php

namespace App\Http\Requests\Backups;

use Illuminate\Foundation\Http\FormRequest;

class DeleteBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.backups-health.delete') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
