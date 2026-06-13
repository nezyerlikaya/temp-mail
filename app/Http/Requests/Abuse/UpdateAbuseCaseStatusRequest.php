<?php

namespace App\Http\Requests\Abuse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAbuseCaseStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update abuse case status') ?? false;
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in(['new', 'reviewing', 'awaiting_information'])]];
    }
}
