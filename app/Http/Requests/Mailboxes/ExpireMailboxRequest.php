<?php

namespace App\Http\Requests\Mailboxes;

use Illuminate\Foundation\Http\FormRequest;

class ExpireMailboxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('expire mailbox') ?? false;
    }

    public function rules(): array
    {
        return ['confirmation' => ['required', 'in:EXPIRE']];
    }
}
