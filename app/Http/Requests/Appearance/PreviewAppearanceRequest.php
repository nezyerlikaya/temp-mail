<?php

namespace App\Http\Requests\Appearance;

use App\Services\Appearance\AppearancePreviewService;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewAppearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('preview appearance') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $preview = app(AppearancePreviewService::class);

        return [
            'theme' => ['required', 'string', Rule::in(app(ThemeRegistry::class)->slugs())],
            'mode' => ['nullable', 'string', Rule::in(array_keys($preview->modes()))],
            'device' => ['nullable', 'string', Rule::in(array_keys($preview->devices()))],
            'direction' => ['nullable', 'string', Rule::in(array_keys($preview->directions()))],
        ];
    }
}
