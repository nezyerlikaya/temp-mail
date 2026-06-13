<?php

namespace App\Http\Requests\Abuse;

use Illuminate\Foundation\Http\FormRequest;

class AddAbuseEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage abuse evidence') ?? false;
    }

    public function rules(): array
    {
        return [
            'media_asset_id' => ['required', 'integer', 'exists:media_assets,id'],
            'label' => ['nullable', 'string', 'max:160'],
        ];
    }
}
