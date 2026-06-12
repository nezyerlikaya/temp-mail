<?php

namespace App\Http\Requests\Mail;

use Illuminate\Validation\Rule;

trait SmtpConnectionRules
{
    /** @return array<string, mixed> */
    protected function smtpRules(bool $passwordRequired): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'domain_id' => ['nullable', 'integer', Rule::exists('domains', 'id')],
            'host' => ['required', 'string', 'max:255', 'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'encryption' => ['required', Rule::in(['none', 'ssl', 'tls'])],
            'username' => ['required', 'string', 'max:255'],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'string', 'max:4096'],
            'from_email' => ['required', 'email:rfc', 'max:255'],
            'from_name' => ['required', 'string', 'max:120'],
            'reply_to_email' => ['nullable', 'email:rfc', 'max:255'],
            'connection_timeout' => ['required', 'integer', 'between:3,120'],
            'validate_certificate' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'is_default' => ['required', 'boolean'],
        ];
    }

    protected function prepareSmtpInput(): void
    {
        $this->merge([
            'domain_id' => $this->input('domain_id') ?: null,
            'validate_certificate' => $this->boolean('validate_certificate'),
            'is_active' => $this->boolean('is_active'),
            'is_default' => $this->boolean('is_default'),
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'host.regex' => 'Enter a valid SMTP hostname.',
            'password.required' => 'Enter the provider password when creating an SMTP connection.',
            'from_email.email' => 'Enter a valid transactional from email address.',
            'reply_to_email.email' => 'Enter a valid reply-to email address.',
        ];
    }
}
