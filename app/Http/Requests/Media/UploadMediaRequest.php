<?php

namespace App\Http\Requests\Media;

use App\Services\Media\MediaValidationService;
use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.media-library.upload') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $validation = app(MediaValidationService::class);

        return [
            'file' => ['required', 'file', 'max:'.$validation->maxKilobytes(), 'mimetypes:'.implode(',', $validation->allowedMimeTypes())],
            'title' => ['nullable', 'string', 'max:120'],
            'alt_text' => ['nullable', 'string', 'max:160'],
            'caption' => ['nullable', 'string', 'max:500'],
            'type' => ['nullable', 'in:image,document,avatar,seo'],
            'status' => ['nullable', 'in:active,hidden'],
        ];
    }
}
