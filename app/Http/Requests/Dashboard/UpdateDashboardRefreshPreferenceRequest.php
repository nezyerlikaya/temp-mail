<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDashboardRefreshPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view live metrics') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'auto_refresh' => ['required', 'boolean'],
            'interval' => ['required', 'integer', Rule::in([15, 30, 60])],
        ];
    }
}
