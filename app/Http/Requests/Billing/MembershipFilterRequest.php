<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MembershipFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view memberships') ?? false;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['nullable'],
            'status' => ['nullable', Rule::in(['all', 'active', 'expiring', 'expired', 'canceled'])],
            'expiring' => ['nullable', Rule::in(['all', 'soon'])],
            'user' => ['nullable', 'string', 'max:120'],
        ];
    }
}
