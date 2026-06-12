<?php

namespace App\Http\Requests\Mail;

use Illuminate\Foundation\Http\FormRequest;

class RunMailInfrastructureChecksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('run infrastructure health checks') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
