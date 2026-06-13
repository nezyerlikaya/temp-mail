<?php

namespace App\Http\Requests\Typography;

use Illuminate\Foundation\Http\FormRequest;

class ResetLocaleFontOverrideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reset locale font override') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
