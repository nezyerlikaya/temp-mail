<?php

namespace App\Http\Requests\Mailboxes;

use Illuminate\Foundation\Http\FormRequest;

class RunMailboxCleanupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('run mailbox cleanup') ?? false;
    }

    public function rules(): array
    {
        return ['confirmation' => ['required', 'in:CLEAN EXPIRED']];
    }
}
