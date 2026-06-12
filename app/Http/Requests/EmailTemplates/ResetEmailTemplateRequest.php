<?php

namespace App\Http\Requests\EmailTemplates;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class ResetEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return ($user?->can('admin.email-templates.reset') ?? false)
            && in_array($user->role, [UserRole::Owner->value, UserRole::Admin->value], true);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'confirm_reset' => ['accepted'],
        ];
    }
}
