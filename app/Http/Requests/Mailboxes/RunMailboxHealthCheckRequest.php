<?php

namespace App\Http\Requests\Mailboxes;

use Illuminate\Foundation\Http\FormRequest;

class RunMailboxHealthCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('run mailbox delivery health checks') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
