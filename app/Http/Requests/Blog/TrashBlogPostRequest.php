<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

class TrashBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.blog-studio.trash') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'confirm_trash' => ['accepted'],
        ];
    }
}
