<?php

namespace App\Http\Requests\Themes;

use App\Services\Themes\ThemeRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivateThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('activate theme') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'theme' => ['required', 'string', Rule::in(app(ThemeRegistry::class)->slugs())],
            'confirmation' => ['required', 'accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'confirmation.accepted' => 'Confirm the theme activation before changing the public website theme.',
        ];
    }
}
