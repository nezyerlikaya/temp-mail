<?php

namespace App\Services\Translations;

use App\Models\TranslationSource;
use Illuminate\Validation\ValidationException;

class TranslationValueSanitizer
{
    public function sanitize(TranslationSource $source, ?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        if ($value === null || $value === '') {
            return null;
        }

        if (preg_match('/(<\?php|<\?=|@php|@endphp|@foreach|@if|@include|@extends|@component|@inject|{{|}}|{!!|!!})/i', $value) === 1) {
            throw ValidationException::withMessages([
                "translations.{$source->id}.value" => 'Translations cannot execute PHP, Blade directives, or raw Blade echoes.',
            ]);
        }

        if ($source->value_type !== 'rich_text') {
            return strip_tags($value);
        }

        if (preg_match('/<\s*script\b|\son[a-z]+\s*=|javascript:|data:text\/html/i', $value) === 1) {
            throw ValidationException::withMessages([
                "translations.{$source->id}.value" => 'Rich text translations cannot contain executable HTML.',
            ]);
        }

        $clean = strip_tags($value, '<p><br><strong><b><em><i><u><a><ul><ol><li>');
        $clean = preg_replace('/\s(style|class|id)\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $clean) ?? $clean;

        return trim($clean);
    }
}
