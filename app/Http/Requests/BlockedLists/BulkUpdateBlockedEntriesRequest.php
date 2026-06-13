<?php

namespace App\Http\Requests\BlockedLists;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateBlockedEntriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('bulk modify blocked entries') ?? false;
    }

    public function rules(): array
    {
        return [
            'entry_ids' => ['required', 'array', 'min:1', 'max:100'],
            'entry_ids.*' => ['integer', 'exists:blocked_list_entries,id'],
            'bulk_action' => ['required', Rule::in(['activate', 'deactivate', 'expire', 'update_expiration'])],
            'expires_at' => ['nullable', 'required_if:bulk_action,update_expiration', 'date', 'after:today'],
        ];
    }
}
