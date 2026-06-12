<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

class DeleteBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.blog-studio.delete') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'confirm_delete' => ['accepted'],
        ];
    }
}
