<?php

namespace App\Services\Seo;

use Illuminate\Validation\ValidationException;

class SeoCanonicalValidator
{
    public function validate(?string $url): void
    {
        if (blank($url)) {
            return;
        }

        $value = trim((string) $url);

        if (str_starts_with(strtolower($value), 'javascript:') || str_starts_with(strtolower($value), 'data:')) {
            throw ValidationException::withMessages([
                'canonical_url' => 'Canonical URL must not use an executable scheme.',
            ]);
        }

        if (str_starts_with($value, '/')) {
            return;
        }

        $parts = parse_url($value);

        if (! is_array($parts) || ! in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true) || blank($parts['host'] ?? null)) {
            throw ValidationException::withMessages([
                'canonical_url' => 'Canonical URL must be a relative path or a valid http(s) URL.',
            ]);
        }
    }
}
