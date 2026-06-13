<?php

namespace App\Http\Requests\PublicSite;

use Illuminate\Foundation\Http\FormRequest;

class RefreshPublicInboxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'access_token' => ['required', 'string', 'size:64'],
            'return_to' => ['nullable', 'url'],
        ];
    }
}
