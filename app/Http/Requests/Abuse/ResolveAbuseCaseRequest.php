<?php

namespace App\Http\Requests\Abuse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveAbuseCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resolve or reject abuse case') ?? false;
    }

    public function rules(): array
    {
        return [
            'resolution_outcome' => ['required', Rule::in(['no_action', 'warning_issued', 'mailbox_locked', 'mailbox_expired', 'user_suspended', 'sender_or_domain_blocked', 'content_removed_readiness', 'referred_for_legal_review'])],
            'resolution_reason' => ['required', 'string', 'min:10', 'max:5000'],
            'resolution_summary' => ['nullable', 'string', 'max:2000'],
            'reporter_response_subject' => ['nullable', 'string', 'max:160'],
            'reporter_response_body' => ['nullable', 'string', 'max:5000'],
            'confirm_resolution' => ['accepted'],
        ];
    }
}
