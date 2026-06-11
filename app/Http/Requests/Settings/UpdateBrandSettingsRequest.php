<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.settings.manage') === true;
    }

    public function rules(): array
    {
        return [
            'logo_media_id' => ['nullable', 'integer', 'min:1'],
            'favicon_media_id' => ['nullable', 'integer', 'min:1'],
            'app_icon_media_id' => ['nullable', 'integer', 'min:1'],
            'public_site_name' => ['required', 'string', 'max:120'],
            'footer_brand_text' => ['required', 'string', 'max:255'],
        ];
    }
}
