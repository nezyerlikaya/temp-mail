<?php

namespace App\Http\Requests\BlockedLists;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlockedListFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view blocked lists') ?? false;
    }

    public function rules(): array
    {
        return [
            'group' => ['nullable', Rule::in(['senders', 'domains', 'recipients', 'ip-rules', 'comment-rules', 'all'])],
            'entry_type' => ['nullable', 'string', 'max:48'],
            'status' => ['nullable', Rule::in(['all', 'active', 'inactive', 'expired'])],
            'source' => ['nullable', Rule::in(['all', 'manual', 'abuse_report', 'security_review', 'comment_moderation'])],
            'expiry' => ['nullable', Rule::in(['all', 'expires', 'expiring_soon', 'expired'])],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            'q' => ['nullable', 'string', 'max:120'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        return [
            'group' => $this->validated('group', 'senders'),
            'entry_type' => $this->validated('entry_type', 'all'),
            'status' => $this->validated('status', 'all'),
            'source' => $this->validated('source', 'all'),
            'expiry' => $this->validated('expiry', 'all'),
            'created_by' => $this->validated('created_by'),
            'q' => $this->validated('q'),
        ];
    }
}
