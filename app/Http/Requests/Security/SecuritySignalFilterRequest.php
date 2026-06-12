<?php

namespace App\Http\Requests\Security;

use App\Services\Security\AbuseSignalService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SecuritySignalFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view security operations') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $options = app(AbuseSignalService::class)->filterOptions();

        return [
            'severity' => ['nullable', Rule::in(['all', ...array_keys($options['severities'])])],
            'signal_type' => ['nullable', Rule::in(['all', ...array_keys($options['types'])])],
            'source_module' => ['nullable', Rule::in(['all', ...array_keys($options['modules'])])],
            'status' => ['nullable', Rule::in(['all', ...array_keys($options['statuses'])])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }
}
