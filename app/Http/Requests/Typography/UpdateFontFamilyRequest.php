<?php

namespace App\Http\Requests\Typography;

use App\Services\Typography\FontRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFontFamilyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage font families') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(FontRegistry $registry): array
    {
        return [
            'font_display' => ['required', Rule::in($registry->fontDisplayOptions())],
            'local_file_ready' => ['nullable', 'boolean'],
            'media_ready' => ['nullable', 'boolean'],
        ];
    }
}
