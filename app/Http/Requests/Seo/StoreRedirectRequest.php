<?php

namespace App\Http\Requests\Seo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.seo-growth-center.redirects') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'source_path' => ['required', 'string', 'max:255', 'regex:/^\/?[A-Za-z0-9\-._~\/]+$/'],
            'target_url' => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'integer', Rule::in([301, 302])],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
