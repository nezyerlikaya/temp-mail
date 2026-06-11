<?php

namespace App\Http\Requests\Backups;

use Illuminate\Foundation\Http\FormRequest;

class RunHealthCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.backups-health.run-health') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
