<?php

namespace App\Services\Seo;

use Illuminate\Validation\ValidationException;

class SeoSchemaValidator
{
    /** @return array<string, mixed>|null */
    public function sanitize(?string $json): ?array
    {
        if (blank($json)) {
            return null;
        }

        try {
            $decoded = json_decode((string) $json, true, 24, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ValidationException::withMessages([
                'schema_json_text' => 'Schema JSON-LD must be valid JSON.',
            ]);
        }

        if (! is_array($decoded) || array_is_list($decoded)) {
            throw ValidationException::withMessages([
                'schema_json_text' => 'Schema JSON-LD must be a JSON object.',
            ]);
        }

        $encoded = json_encode($decoded, JSON_THROW_ON_ERROR);
        if (preg_match('/<\\/?script|javascript:|data:text\\/html/i', $encoded) === 1) {
            throw ValidationException::withMessages([
                'schema_json_text' => 'Schema JSON-LD cannot contain executable code.',
            ]);
        }

        return $decoded;
    }
}
