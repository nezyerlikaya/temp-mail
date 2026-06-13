<?php

namespace App\Http\Requests\BlockedLists;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportBlockedEntriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('export blocked entries') ?? false;
    }

    public function rules(): array
    {
        return [
            'entry_type' => ['nullable', 'string', 'max:48'],
            'status' => ['nullable', Rule::in(['all', 'active', 'inactive', 'expired'])],
            'source' => ['nullable', Rule::in(['all', 'manual', 'abuse_report', 'security_review', 'comment_moderation'])],
            'include_sensitive_ip' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        return [
            'entry_type' => $this->validated('entry_type', 'all') ?: 'all',
            'status' => $this->validated('status', 'all') ?: 'all',
            'source' => $this->validated('source', 'all') ?: 'all',
        ];
    }
}
