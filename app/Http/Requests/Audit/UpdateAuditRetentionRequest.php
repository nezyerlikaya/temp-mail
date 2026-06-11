<?php

namespace App\Http\Requests\Audit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuditRetentionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.activity-audit-logs.manage-retention') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'retention_days' => ['required', 'integer', 'min:30', 'max:3650'],
            'preserve_critical' => ['nullable', 'boolean'],
        ];
    }
}
