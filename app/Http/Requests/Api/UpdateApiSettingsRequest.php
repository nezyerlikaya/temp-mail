<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApiSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage API globally') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'api_enabled' => $this->boolean('api_enabled'),
            'free_api_enabled' => $this->boolean('free_api_enabled'),
            'premium_api_enabled' => $this->boolean('premium_api_enabled'),
            'business_api_enabled' => $this->boolean('business_api_enabled'),
        ]);
    }

    public function rules(): array
    {
        return [
            'api_enabled' => ['boolean'],
            'free_api_enabled' => ['boolean'],
            'premium_api_enabled' => ['boolean'],
            'business_api_enabled' => ['boolean'],
        ];
    }
}
