<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExtendMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('extend membership') ?? false;
    }

    public function rules(): array
    {
        return [
            'preset' => ['required', Rule::in(['one_month', 'custom'])],
            'ends_at' => ['required_if:preset,custom', 'nullable', 'date', 'after:now'],
            'grace_period_days' => ['nullable', 'integer', 'min:0', 'max:3'],
        ];
    }
}
