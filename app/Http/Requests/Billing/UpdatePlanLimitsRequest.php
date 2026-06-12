<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanLimitsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update plan limits') ?? false;
    }

    protected function prepareForValidation(): void
    {
        foreach (['custom_alias_allowed', 'custom_domain_allowed', 'api_access_allowed', 'ads_enabled'] as $field) {
            $this->merge([$field => $this->boolean($field)]);
        }
    }

    public function rules(): array
    {
        return [
            'maximum_active_inboxes' => ['required', 'integer', 'min:1', 'max:10000'],
            'inbox_lifetime_minutes' => ['required', 'integer', 'min:5', 'max:525600'],
            'maximum_messages_per_inbox' => ['required', 'integer', 'min:1', 'max:100000'],
            'maximum_message_size_kb' => ['required', 'integer', 'min:64', 'max:102400'],
            'custom_alias_allowed' => ['required', 'boolean'],
            'custom_domain_allowed' => ['required', 'boolean'],
            'api_access_allowed' => ['required', 'boolean'],
            'api_request_limit' => ['required', 'integer', 'min:0', 'max:10000000'],
            'ads_enabled' => ['required', 'boolean'],
        ];
    }
}
