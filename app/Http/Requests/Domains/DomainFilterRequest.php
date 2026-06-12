<?php

namespace App\Http\Requests\Domains;

use App\Services\Domains\DomainStatusResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DomainFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view domains') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['all', ...array_keys(app(DomainStatusResolver::class)->options())])],
            'active' => ['nullable', Rule::in(['all', 'active', 'passive'])],
            'visibility' => ['nullable', Rule::in(['all', 'public', 'private'])],
            'dns' => ['nullable', Rule::in(['all', 'ready', 'needs_dns'])],
            'per_page' => ['nullable', 'integer', 'min:6', 'max:48'],
        ];
    }
}
