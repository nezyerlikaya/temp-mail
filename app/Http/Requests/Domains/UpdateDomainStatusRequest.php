<?php

namespace App\Http\Requests\Domains;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDomainStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('activate deactivate domain') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status_action' => ['required', Rule::in(['activate', 'deactivate'])],
        ];
    }
}
