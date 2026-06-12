<?php

namespace App\Http\Requests\Mail;

use Illuminate\Foundation\Http\FormRequest;

class SetDefaultSmtpConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('set default SMTP connection') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
