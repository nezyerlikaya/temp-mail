<?php

namespace App\Http\Requests\Mail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInboundMailConnectionRequest extends FormRequest
{
    use InboundMailConnectionRules;

    public function authorize(): bool
    {
        return $this->user()?->can('create update inbound connection') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareConnectionInput();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...$this->connectionRules(true),
            'name' => [
                ...$this->connectionRules(true)['name'],
                Rule::unique('inbound_mail_connections', 'name')->where('domain_id', $this->integer('domain_id')),
            ],
        ];
    }
}
