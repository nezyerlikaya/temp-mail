<?php

namespace App\Http\Requests\Domains;

use Illuminate\Foundation\Http\FormRequest;

class RunDomainDnsCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('run DNS checks') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
