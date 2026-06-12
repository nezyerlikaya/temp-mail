<?php

namespace App\Http\Requests\Mailboxes;

use Illuminate\Foundation\Http\FormRequest;

class UnlockMailboxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('lock unlock mailbox') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
