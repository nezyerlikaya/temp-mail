<?php

namespace App\Http\Requests\Pages;

use Illuminate\Foundation\Http\FormRequest;

class TrashPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.page-studio.trash') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'confirm_trash' => ['accepted'],
        ];
    }
}
