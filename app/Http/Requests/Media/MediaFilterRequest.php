<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class MediaFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.media-library.view') ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'in:all,image,document,avatar,seo'],
            'status' => ['nullable', 'in:all,active,hidden,trashed'],
            'usage' => ['nullable', 'in:all,orphaned,in-use'],
            'uploader' => ['nullable', 'integer', 'exists:users,id'],
            'date' => ['nullable', 'in:all,today,week'],
        ];
    }
}
