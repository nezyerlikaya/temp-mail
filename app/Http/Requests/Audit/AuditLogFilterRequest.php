<?php

namespace App\Http\Requests\Audit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuditLogFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.activity-audit-logs.view') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'module' => ['nullable', 'string', 'max:80'],
            'actor' => ['nullable', 'string', 'max:120'],
            'action' => ['nullable', 'string', 'max:120'],
            'severity' => ['nullable', Rule::in(['info', 'warning', 'critical'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }
}
