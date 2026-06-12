<?php

namespace App\Http\Requests\Pages;

use Illuminate\Foundation\Http\FormRequest;

class HidePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.page-studio.hide') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [];
    }
}
