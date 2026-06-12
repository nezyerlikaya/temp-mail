<?php

namespace App\Http\Requests\Mailboxes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MessageActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage message state') ?? false;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['read', 'unread', 'delete'])],
            'confirmation' => [Rule::requiredIf($this->input('action') === 'delete'), 'nullable', 'in:DELETE'],
        ];
    }
}
