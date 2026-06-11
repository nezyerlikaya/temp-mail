<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class DetachMediaUsageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.media-library.update') === true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'module' => ['required', 'string', 'max:64'],
            'usage_context' => ['required', 'string', 'max:64'],
            'slot' => ['required', 'string', 'max:96'],
            'usable_type' => ['nullable', 'string', 'max:120'],
            'usable_id' => ['nullable', 'string', 'max:64'],
        ];
    }
}
