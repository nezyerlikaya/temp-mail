<?php

namespace App\Http\Requests\Mail;

use Illuminate\Validation\Rule;

trait InboundMailConnectionRules
{
    /** @return array<string, mixed> */
    protected function connectionRules(bool $passwordRequired): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'domain_id' => ['required', 'integer', Rule::exists('domains', 'id')],
            'host' => ['required', 'string', 'max:255', 'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'encryption' => ['required', Rule::in(['none', 'ssl', 'tls'])],
            'username' => ['required', 'string', 'max:255'],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'string', 'max:4096'],
            'mailbox' => ['required', 'string', 'max:255', 'not_regex:/[\r\n]/'],
            'connection_timeout' => ['required', 'integer', 'between:3,120'],
            'validate_certificate' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareConnectionInput(): void
    {
        $this->merge([
            'validate_certificate' => $this->boolean('validate_certificate'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'domain_id.exists' => 'Create a domain before attaching an inbound mail connection.',
            'host.regex' => 'Enter a valid inbound mail hostname.',
            'password.required' => 'Enter the provider password when creating a connection.',
            'validate_certificate.required' => 'Choose whether server certificates must be validated.',
        ];
    }
}
