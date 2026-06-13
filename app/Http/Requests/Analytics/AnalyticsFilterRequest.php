<?php

namespace App\Http\Requests\Analytics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyticsFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view analytics') ?? false;
    }

    public function rules(): array
    {
        return [
            'preset' => ['nullable', Rule::in(['today', 'last_7_days', 'last_30_days', 'custom'])],
            'date_from' => ['nullable', 'date', 'required_if:preset,custom'],
            'date_to' => ['nullable', 'date', 'required_if:preset,custom'],
            'locale_id' => ['nullable'],
            'domain_id' => ['nullable'],
        ];
    }
}
