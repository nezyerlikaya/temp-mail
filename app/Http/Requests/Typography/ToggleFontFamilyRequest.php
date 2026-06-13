<?php

namespace App\Http\Requests\Typography;

use Illuminate\Foundation\Http\FormRequest;

class ToggleFontFamilyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage font families') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
