<?php

namespace App\Http\Requests\Mail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSmtpConnectionRequest extends FormRequest
{
    use SmtpConnectionRules;

    public function authorize(): bool
    {
        return $this->user()?->can('create update SMTP connection') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareSmtpInput();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...$this->smtpRules(true),
            'name' => [...$this->smtpRules(true)['name'], Rule::unique('smtp_connections', 'name')],
        ];
    }
}
