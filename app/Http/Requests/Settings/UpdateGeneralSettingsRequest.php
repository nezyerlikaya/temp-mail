<?php

namespace App\Http\Requests\Settings;

use App\Services\Settings\SettingsResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.settings.manage') === true;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:120'],
            'site_tagline' => ['nullable', 'string', 'max:180'],
            'admin_email' => ['required', 'email', 'max:255'],
            'support_email' => ['required', 'email', 'max:255'],
            'abuse_email' => ['required', 'email', 'max:255'],
            'default_language' => ['required', Rule::in(array_keys(app(SettingsResolver::class)->activeLanguages()))],
            'default_timezone' => ['required', 'timezone:all'],
            'date_format' => ['required', Rule::in(['M j, Y', 'Y-m-d', 'd/m/Y', 'm/d/Y'])],
            'time_format' => ['required', Rule::in(['H:i', 'h:i A'])],
        ];
    }
}
