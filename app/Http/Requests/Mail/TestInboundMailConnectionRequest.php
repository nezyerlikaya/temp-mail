<?php

namespace App\Http\Requests\Mail;

use Illuminate\Foundation\Http\FormRequest;

class TestInboundMailConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('test inbound connection') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
