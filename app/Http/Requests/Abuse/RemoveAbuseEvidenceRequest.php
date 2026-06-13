<?php

namespace App\Http\Requests\Abuse;

use Illuminate\Foundation\Http\FormRequest;

class RemoveAbuseEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage abuse evidence') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
