<?php

namespace App\Http\Requests\Abuse;

use Illuminate\Foundation\Http\FormRequest;

class AddAbuseCaseNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('add internal abuse notes') ?? false;
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'min:3', 'max:5000']];
    }
}
