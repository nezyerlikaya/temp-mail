<?php

namespace App\Http\Requests\Mailboxes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMailboxRulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update mailbox rules') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['auto_delete_expired' => $this->boolean('auto_delete_expired')]);
    }

    public function rules(): array
    {
        return [
            'guest_lifetime_minutes' => ['required', 'integer', 'min:15', 'max:43200'],
            'registered_lifetime_minutes' => ['required', 'integer', 'min:60', 'max:525600'],
            'premium_lifetime_minutes' => ['required', 'integer', 'min:60', 'max:525600'],
            'maximum_active_mailboxes' => ['required', 'integer', 'min:1', 'max:1000'],
            'maximum_messages_per_inbox' => ['required', 'integer', 'min:1', 'max:10000'],
            'maximum_message_size_kb' => ['required', 'integer', 'min:64', 'max:102400'],
            'attachment_policy' => ['required', Rule::in(['disabled', 'metadata_only'])],
            'auto_delete_expired' => ['required', 'boolean'],
            'expired_cleanup_delay_hours' => ['required', 'integer', 'min:1', 'max:8760'],
            'inbox_refresh_rate_limit' => ['required', 'integer', 'min:5', 'max:3600'],
            'random_alias_length' => ['required', 'integer', 'min:6', 'max:64'],
            'random_alias_format' => ['required', Rule::in(['alphanumeric', 'letters', 'words'])],
        ];
    }
}
