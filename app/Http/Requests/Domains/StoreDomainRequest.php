<?php

namespace App\Http\Requests\Domains;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDomainRequest extends FormRequest
{
    use DomainRules;

    public function authorize(): bool
    {
        return $this->user()?->can('create domain') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->baseRules();
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->boolean('is_default') && ! $this->boolean('is_active')) {
                    $validator->errors()->add('is_default', 'The default domain must be active.');
                }
            },
        ];
    }
}
