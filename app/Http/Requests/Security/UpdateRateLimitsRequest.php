<?php

namespace App\Http\Requests\Security;

use App\Services\Security\RateLimitPolicyStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRateLimitsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage rate limits') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $actions = array_keys(app(RateLimitPolicyStore::class)->defaultPolicies());
        $strategies = array_keys(app(RateLimitPolicyStore::class)->strategies());

        return [
            'policies' => ['required', 'array'],
            'policies.*.max_attempts' => ['required', 'integer', 'min:1', 'max:10000'],
            'policies.*.window_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'policies.*.cooldown_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'policies.*.strategy' => ['required', Rule::in($strategies)],
            'policies.*.is_active' => ['nullable', 'boolean'],
            'policies.*.key' => ['nullable', Rule::in($actions)],
        ];
    }
}
