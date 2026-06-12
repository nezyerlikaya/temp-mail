<?php

namespace App\Http\Requests\Mailboxes;

use Illuminate\Foundation\Http\FormRequest;

class EmptyMailboxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('empty mailbox') ?? false;
    }

    public function rules(): array
    {
        return ['confirmation' => ['required', 'in:EMPTY']];
    }
}
