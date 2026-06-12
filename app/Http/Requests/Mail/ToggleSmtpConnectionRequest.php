<?php

namespace App\Http\Requests\Mail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleSmtpConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('activate deactivate SMTP connection') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['status_action' => ['required', Rule::in(['activate', 'deactivate'])]];
    }
}
