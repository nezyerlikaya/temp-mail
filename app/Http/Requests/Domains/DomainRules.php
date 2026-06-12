<?php

namespace App\Http\Requests\Domains;

use App\Services\Domains\DomainStatusResolver;
use Illuminate\Validation\Rule;

trait DomainRules
{
    /** @return array<string, mixed> */
    protected function baseRules(?int $ignoreId = null): array
    {
        $unique = Rule::unique('domains', 'domain_name');

        if ($ignoreId !== null) {
            $unique->ignore($ignoreId);
        }

        return [
            'domain_name' => ['required', 'string', 'max:253', 'regex:/^(?!-)(?:[a-z0-9-]{1,63}\.)+[a-z]{2,63}$/i', $unique],
            'display_name' => ['required', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
            'catch_all_ready' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:9999'],
            'status' => ['required', Rule::in(array_keys(app(DomainStatusResolver::class)->options()))],
        ];
    }
}
