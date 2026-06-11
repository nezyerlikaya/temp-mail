<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLegalSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.settings.manage') === true;
    }

    public function rules(): array
    {
        return collect(['privacy', 'terms', 'cookie', 'abuse', 'dmca', 'contact'])
            ->mapWithKeys(fn (string $key): array => [$key.'_page_id' => ['nullable', 'integer', 'min:1']])
            ->all();
    }
}
