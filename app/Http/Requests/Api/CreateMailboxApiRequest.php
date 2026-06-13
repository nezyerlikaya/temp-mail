<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateMailboxApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'domain' => ['nullable', 'string', 'max:253'],
            'local_part' => ['nullable', 'string', 'max:64'],
        ];
    }
}
