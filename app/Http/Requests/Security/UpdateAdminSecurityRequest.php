<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminSecurityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage admin security') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'password_min_length' => ['required', 'integer', 'min:8', 'max:128'],
            'password_letters' => ['nullable', 'boolean'],
            'password_numbers' => ['nullable', 'boolean'],
            'password_symbols' => ['nullable', 'boolean'],
            'require_email_verification' => ['nullable', 'boolean'],
            'admin_session_lifetime' => ['required', 'integer', 'min:15', 'max:1440'],
            'login_alerts' => ['nullable', 'boolean'],
            'admin_ip_allowlist_ready' => ['nullable', 'boolean'],
            'require_2fa_readiness' => ['nullable', 'boolean'],
            'critical_notifications_ready' => ['nullable', 'boolean'],
        ];
    }
}
