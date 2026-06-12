<?php

namespace App\Http\Requests\Notifications;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view notifications') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['open', 'unread', 'read', 'archived'])],
            'severity' => ['nullable', Rule::in(['all', 'info', 'warning', 'critical'])],
            'module' => ['nullable', Rule::in(['all', 'content', 'trust', 'mail-infrastructure', 'system', 'billing'])],
        ];
    }

    /** @return array{status: string, severity: string, module: string} */
    public function filters(): array
    {
        return [
            'status' => (string) $this->validated('status', 'open'),
            'severity' => (string) $this->validated('severity', 'all'),
            'module' => (string) $this->validated('module', 'all'),
        ];
    }
}
