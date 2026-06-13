<?php

namespace App\Http\Requests\Integrations;

use App\Services\Integrations\IntegrationRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestIntegrationConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('test integration connection') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'environment' => ['required', 'string', Rule::in(array_keys(app(IntegrationRegistry::class)->environments()))],
        ];
    }
}
