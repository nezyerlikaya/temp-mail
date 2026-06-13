<?php

namespace App\Http\Requests\BlockedLists;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestBlockedEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('run enforcement test') ?? false;
    }

    public function rules(): array
    {
        return [
            'entry_type' => ['required', Rule::in(['sender_email', 'sender_domain', 'recipient_email_pattern', 'recipient_domain', 'ip_address', 'comment_email', 'blocked_phrase'])],
            'value' => ['required', 'string', 'max:2000'],
        ];
    }
}
