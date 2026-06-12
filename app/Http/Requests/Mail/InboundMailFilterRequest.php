<?php

namespace App\Http\Requests\Mail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InboundMailFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view inbound mail settings') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['all', 'not_tested', 'connected', 'failed', 'disabled'])],
            'domain_id' => ['nullable', Rule::when($this->input('domain_id') !== 'all', ['integer', Rule::exists('domains', 'id')])],
        ];
    }
}
