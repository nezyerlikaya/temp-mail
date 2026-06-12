<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestSecurityProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('test security provider') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'target' => ['required', Rule::in(['bot_protection', 'akismet'])],
        ];
    }
}
