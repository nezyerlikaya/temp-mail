<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBotProtectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update security settings') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'provider' => ['required', Rule::in(['none', 'turnstile', 'recaptcha'])],
            'recaptcha_mode' => ['nullable', Rule::in(['v2_checkbox', 'v3_score'])],
            'site_key' => ['nullable', 'string', 'max:500'],
            'secret_key' => ['nullable', 'string', 'max:500'],
            'minimum_score' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'fail_mode' => ['required', Rule::in(['block', 'challenge', 'log_only'])],
            'is_active' => ['nullable', 'boolean'],
            'protected_forms' => ['nullable', 'array'],
            'protected_forms.*' => ['string', Rule::in(['login', 'register', 'forgot_password', 'contact', 'comments', 'mailbox_creation', 'api_access'])],
        ];
    }
}
