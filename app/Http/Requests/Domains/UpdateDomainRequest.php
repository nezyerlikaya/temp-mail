<?php

namespace App\Http\Requests\Domains;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateDomainRequest extends FormRequest
{
    use DomainRules;

    public function authorize(): bool
    {
        return $this->user()?->can('update domain') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->baseRules((int) $this->route('domain')?->getKey());
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
