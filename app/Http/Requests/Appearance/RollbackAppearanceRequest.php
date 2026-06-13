<?php

namespace App\Http\Requests\Appearance;

use App\Models\AppearanceVersion;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RollbackAppearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('rollback appearance') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'theme' => ['required', 'string', Rule::in(app(ThemeRegistry::class)->slugs())],
            'version_id' => ['required', 'integer', Rule::exists(AppearanceVersion::class, 'id')],
            'confirmation' => ['required', 'accepted'],
        ];
    }
}
