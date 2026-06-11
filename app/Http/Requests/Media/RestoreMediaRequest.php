<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class RestoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.media-library.restore') ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [];
    }
}
