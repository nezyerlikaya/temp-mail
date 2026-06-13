<?php

namespace App\Http\Requests\Appearance;

use App\Services\Themes\ThemeRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublishAppearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('publish appearance') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'theme' => ['required', 'string', Rule::in(app(ThemeRegistry::class)->slugs())],
            'confirmation' => ['required', 'accepted'],
        ];
    }
}
