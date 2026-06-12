<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;

class ForceLogoutSessionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('force logout sessions') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'confirmation' => ['required', 'in:LOG OUT SESSIONS'],
        ];
    }
}
