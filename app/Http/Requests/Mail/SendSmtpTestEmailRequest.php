<?php

namespace App\Http\Requests\Mail;

use Illuminate\Foundation\Http\FormRequest;

class SendSmtpTestEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('send SMTP test email') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'recipient' => ['required', 'email:rfc', 'max:255'],
        ];
    }
}
