<?php

namespace App\Http\Requests\Integrations;

use App\Services\Integrations\IntegrationRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('activate deactivate integrations') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'environment' => ['required', Rule::in(array_keys(app(IntegrationRegistry::class)->environments()))],
        ];
    }
}
