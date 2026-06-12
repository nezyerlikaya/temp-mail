<?php

namespace App\Services\EmailTemplates;

use Illuminate\Validation\ValidationException;

class EmailTemplateSanitizer
{
    /** @return array{html: string, plain: string|null} */
    public function sanitize(string $html, ?string $plain = null): array
    {
        $this->rejectExecutableTemplateCode($html."\n".(string) $plain);

        if (preg_match('/<\s*script\b/i', $html) === 1) {
            throw ValidationException::withMessages([
                'html_body' => 'Email HTML cannot contain script tags.',
            ]);
        }

        if (preg_match('/\son[a-z]+\s*=/i', $html) === 1) {
            throw ValidationException::withMessages([
                'html_body' => 'Email HTML cannot contain inline event attributes.',
            ]);
        }

        if (preg_match('/(javascript:|data:text\/html)/i', $html) === 1) {
            throw ValidationException::withMessages([
                'html_body' => 'Email HTML cannot contain executable links.',
            ]);
        }

        $allowedTags = '<p><br><strong><b><em><i><u><a><ul><ol><li><h1><h2><h3><blockquote><code><pre><table><thead><tbody><tr><th><td><hr><span><div>';
        $cleanHtml = strip_tags($html, $allowedTags);
        $cleanHtml = preg_replace('/\s(style|class|id)\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $cleanHtml) ?? $cleanHtml;

        return [
            'html' => trim($cleanHtml),
            'plain' => $plain === null ? null : trim($plain),
        ];
    }

    private function rejectExecutableTemplateCode(string $content): void
    {
        if (preg_match('/(<\?php|<\?=|@php|@endphp|@foreach|@if|{!!|!!})/i', $content) === 1) {
            throw ValidationException::withMessages([
                'html_body' => 'Email templates cannot execute PHP, Blade directives, or raw Blade echoes.',
            ]);
        }
    }
}
