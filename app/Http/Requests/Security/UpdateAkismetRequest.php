<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAkismetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update security settings') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'api_key' => ['nullable', 'string', 'max:500'],
            'site_url' => ['required', 'url', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'protected_comments' => ['nullable', 'boolean'],
            'contact_form_readiness' => ['nullable', 'boolean'],
            'mode' => ['required', Rule::in(['hold_suspicious', 'trash_spam', 'log_only'])],
        ];
    }
}
