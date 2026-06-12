<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TogglePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('activate deactivate plans') ?? false;
    }

    public function rules(): array
    {
        return ['state' => ['required', Rule::in(['active', 'inactive'])]];
    }
}
