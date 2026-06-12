<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class DowngradeMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cancel downgrade membership') ?? false;
    }

    public function rules(): array
    {
        return ['confirmation' => ['required', 'in:DOWNGRADE']];
    }
}
