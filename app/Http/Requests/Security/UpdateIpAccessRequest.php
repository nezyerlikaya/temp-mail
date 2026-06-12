<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIpAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage admin security') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'allowlist' => ['nullable', 'array'],
            'allowlist.*' => ['nullable', 'ip'],
            'blocklist' => ['nullable', 'array'],
            'blocklist.*' => ['nullable', 'ip'],
            'temporary_block_ready' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allowlist' => $this->lines('allowlist'),
            'blocklist' => $this->lines('blocklist'),
        ]);
    }

    /** @return array<int, string> */
    private function lines(string $field): array
    {
        return collect(preg_split('/\R/', (string) $this->input($field, '')))
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
