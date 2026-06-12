<?php

namespace App\Http\Requests\Mailboxes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MailboxFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view mailboxes') ?? false;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['all', 'active', 'expired', 'locked', 'trashed'])],
            'domain_id' => ['nullable', Rule::when($this->input('domain_id') !== 'all', ['integer', Rule::exists('domains', 'id')])],
            'owner' => ['nullable', Rule::in(['all', 'guest', 'registered'])],
            'mailbox_type' => ['nullable', Rule::in(['all', 'guest', 'registered', 'premium', 'system'])],
            'created' => ['nullable', Rule::in(['all', 'today', 'week', 'month'])],
            'per_page' => ['nullable', 'integer', Rule::in([15, 30, 60])],
        ];
    }
}
