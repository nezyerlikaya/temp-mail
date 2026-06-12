<?php

namespace App\Http\Requests\EmailTemplates;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class SendTestEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return ($user?->can('admin.email-templates.send-test') ?? false)
            && in_array($user->role, [UserRole::Owner->value, UserRole::Admin->value], true);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'recipient' => ['required', 'email:rfc', 'max:255'],
        ];
    }
}
