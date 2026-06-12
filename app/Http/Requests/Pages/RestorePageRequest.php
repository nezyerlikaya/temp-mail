<?php

namespace App\Http\Requests\Pages;

use Illuminate\Foundation\Http\FormRequest;

class RestorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.page-studio.restore') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [];
    }
}
