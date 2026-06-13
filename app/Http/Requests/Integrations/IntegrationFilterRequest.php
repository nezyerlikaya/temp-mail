<?php

namespace App\Http\Requests\Integrations;

use App\Services\Integrations\IntegrationRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IntegrationFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view integrations') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $registry = app(IntegrationRegistry::class);

        return [
            'category' => ['nullable', 'string', Rule::in(['all', ...array_keys($registry->categories())])],
            'environment' => ['nullable', 'string', Rule::in(array_keys($registry->environments()))],
            'integration' => ['nullable', 'string', Rule::in($registry->keys())],
        ];
    }
}
