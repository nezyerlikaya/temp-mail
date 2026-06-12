<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GrantMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('grant membership') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('preset') === 'one_month' && blank($this->input('starts_at'))) {
            $this->merge(['starts_at' => now()->format('Y-m-d\TH:i')]);
        }
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')->where(fn ($query) => $query->where('key', '!=', 'free')->where('is_active', true))],
            'preset' => ['required', Rule::in(['one_month', 'custom'])],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required_if:preset,custom', 'nullable', 'date', 'after:starts_at'],
            'grace_period_days' => ['nullable', 'integer', 'min:0', 'max:3'],
            'grant_note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
