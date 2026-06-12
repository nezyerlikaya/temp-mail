<?php

namespace App\Http\Requests\Pages;

use Illuminate\Foundation\Http\FormRequest;

class DeletePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.page-studio.delete') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'confirm_delete' => ['accepted'],
        ];
    }
}
