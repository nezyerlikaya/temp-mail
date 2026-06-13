<?php

namespace App\Http\Requests\Integrations;

use App\Services\Integrations\IntegrationFieldRegistry;
use App\Services\Integrations\IntegrationRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIntegrationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('configure integrations') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $registry = app(IntegrationRegistry::class);
        $fields = app(IntegrationFieldRegistry::class);
        $key = (string) $this->route('integration');

        $rules = [
            'environment' => ['required', Rule::in(array_keys($registry->environments()))],
            'settings' => ['nullable', 'array'],
            'secrets' => ['nullable', 'array'],
        ];

        foreach ($fields->fields($key) as $field) {
            $prefix = $field['type'] === 'secret' ? 'secrets' : 'settings';
            $fieldRules = [(bool) ($field['required'] ?? false) && $field['type'] !== 'secret' ? 'required' : 'nullable'];

            $fieldRules[] = match ($field['type']) {
                'url' => 'url',
                'email' => 'email:rfc,dns',
                'boolean' => 'boolean',
                default => 'string',
            };

            if ($field['type'] === 'select') {
                $fieldRules[] = Rule::in($field['options']);
            }

            $fieldRules[] = 'max:1000';
            $rules[$prefix.'.'.$field['key']] = $fieldRules;
        }

        return $rules;
    }
}
