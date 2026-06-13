<?php

namespace App\Http\Requests\Api;

use App\Services\Api\ApiAccessPolicyService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegenerateApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(ApiAccessPolicyService::class)->canMutate($this->user(), $this->route('apiKey'));
    }

    public function rules(): array
    {
        return ['confirmation' => ['required', Rule::in(['REGENERATE'])]];
    }
}
