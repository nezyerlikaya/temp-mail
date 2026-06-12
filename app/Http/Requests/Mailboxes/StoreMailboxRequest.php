<?php

namespace App\Http\Requests\Mailboxes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMailboxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create mailbox readiness') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->input('user_id') ?: null,
            'locale_id' => $this->input('locale_id') ?: null,
            'expires_at' => $this->input('expires_at') ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'local_part' => ['required', 'string', 'min:1', 'max:64', 'regex:/^[a-zA-Z0-9](?:[a-zA-Z0-9._-]*[a-zA-Z0-9])?$/'],
            'domain_id' => ['required', 'integer', Rule::exists('domains', 'id')],
            'mailbox_type' => ['required', Rule::in(['guest', 'registered', 'premium', 'system'])],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at'), Rule::requiredIf($this->input('mailbox_type') === 'registered')],
            'locale_id' => ['nullable', 'integer', Rule::exists('locales', 'id')],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'local_part.regex' => 'Use letters, numbers, dots, underscores, or hyphens without leading or trailing punctuation.',
            'user_id.required' => 'Select a registered user for a registered mailbox.',
        ];
    }
}
