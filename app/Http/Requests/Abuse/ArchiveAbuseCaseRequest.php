<?php

namespace App\Http\Requests\Abuse;

use Illuminate\Foundation\Http\FormRequest;

class ArchiveAbuseCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reopen or archive abuse case') ?? false;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:10', 'max:2000'], 'confirm_archive' => ['accepted']];
    }
}
