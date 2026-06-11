<?php

namespace App\Http\Requests\Localization;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocaleStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.locale-launch-center.publish') ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'status_action' => ['required', 'in:set_live,take_offline'],
        ];
    }
}
