<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class DeleteMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.media-library.delete') ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'delete_confirmation' => ['required', 'in:DELETE'],
            'confirm_in_use_delete' => ['sometimes', 'accepted'],
        ];
    }
}
