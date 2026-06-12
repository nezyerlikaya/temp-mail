<?php

namespace App\Http\Requests\Mail;

use Illuminate\Foundation\Http\FormRequest;

class TestSmtpConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('test SMTP connection') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
