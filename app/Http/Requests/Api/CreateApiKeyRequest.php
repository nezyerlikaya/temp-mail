<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use App\Services\Api\ApiAccessPolicyService;
use App\Services\Api\ApiScopeRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $owner = $this->owner();

        return $owner instanceof User && app(ApiAccessPolicyService::class)->canCreateFor($this->user(), $owner);
    }

    protected function prepareForValidation(): void
    {
        $ips = collect(preg_split('/[\r\n,]+/', (string) $this->input('ip_allowlist_text')))
            ->map(fn (string $ip): string => trim($ip))
            ->filter()
            ->values()
            ->all();

        $this->merge([
            'user_id' => $this->input('user_id') ?: $this->user()?->id,
            'ip_allowlist' => $ips === [] ? null : $ips,
        ]);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:80'],
            'environment' => ['required', Rule::in(['test', 'live'])],
            'scopes' => ['required', 'array', 'min:1'],
            'scopes.*' => ['required', Rule::in(array_keys(app(ApiScopeRegistry::class)->all()))],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'ip_allowlist' => ['nullable', 'array', 'max:20'],
            'ip_allowlist.*' => ['ip'],
            'ip_allowlist_text' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function owner(): ?User
    {
        return User::query()->find($this->input('user_id') ?: $this->user()?->id);
    }
}
