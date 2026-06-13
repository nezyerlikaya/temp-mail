<?php

namespace App\Http\Requests\BlockedLists;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlockedEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create blocked entry') ?? false;
    }

    public function rules(): array
    {
        return $this->sharedRules();
    }

    /** @return array<string, mixed> */
    protected function sharedRules(): array
    {
        return [
            'entry_type' => ['required', Rule::in(['sender_email', 'sender_domain', 'recipient_email_pattern', 'recipient_domain', 'ip_address', 'comment_email', 'blocked_phrase'])],
            'value' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'min:8', 'max:2000'],
            'source' => ['required', Rule::in(['manual', 'abuse_report', 'security_review', 'comment_moderation'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'expired'])],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'related_abuse_case' => ['nullable', 'string', 'max:32', 'exists:abuse_reports,case_reference'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
