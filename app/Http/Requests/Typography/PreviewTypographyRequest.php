<?php

namespace App\Http\Requests\Typography;

use App\Models\Locale;
use App\Services\Themes\ThemeRegistry;
use App\Services\Typography\FontPreviewService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewTypographyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('preview typography') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'theme' => ['nullable', 'string', Rule::in(app(ThemeRegistry::class)->slugs())],
            'locale' => ['nullable', 'string', Rule::exists('locales', 'locale')],
            'preview_language' => ['nullable', 'string', Rule::in(array_keys(app(FontPreviewService::class)->samples()))],
            'preview_mode' => ['nullable', 'string', Rule::in(array_keys(app(FontPreviewService::class)->modes()))],
            'preview_direction' => ['nullable', 'string', Rule::in(array_keys(app(FontPreviewService::class)->directions()))],
        ];
    }

    public function selectedLocale(): ?Locale
    {
        $locale = $this->validated('locale');

        return $locale ? Locale::query()->where('locale', $locale)->first() : null;
    }
}
