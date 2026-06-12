<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAbuseSignalStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $status = (string) $this->input('status');

        return in_array($status, ['resolved', 'ignored'], true)
            ? ($this->user()?->can('resolve abuse signal') ?? false)
            : ($this->user()?->can('review abuse signal') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['open', 'reviewing', 'resolved', 'ignored'])],
        ];
    }
}
