<?php

namespace App\Http\Requests\Settings;

use App\Services\Settings\SettingsResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocalizationDefaultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.settings.manage') === true;
    }

    public function rules(): array
    {
        $locales = array_keys(app(SettingsResolver::class)->activeLanguages());

        return [
            'default_locale' => ['required', Rule::in($locales)],
            'fallback_locale' => ['required', Rule::in($locales)],
            'rtl_auto_detection' => ['nullable', 'boolean'],
            'missing_locale_behavior' => ['required', Rule::in(['fallback', 'source', 'not_found'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['rtl_auto_detection' => $this->boolean('rtl_auto_detection')]);
    }
}
