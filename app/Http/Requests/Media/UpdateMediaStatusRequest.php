<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.media-library.update') ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:active,hidden'],
        ];
    }
}
