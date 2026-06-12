<?php

namespace App\Http\Requests\Domains;

use Illuminate\Foundation\Http\FormRequest;

class SetDefaultDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('set default domain') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
