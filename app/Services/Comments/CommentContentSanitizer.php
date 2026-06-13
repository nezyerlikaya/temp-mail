<?php

namespace App\Services\Comments;

use Illuminate\Validation\ValidationException;

class CommentContentSanitizer
{
    public function isSafe(string $content): bool
    {
        return preg_match('/(<\?php|<\?=|@php|@endphp|@foreach|@if|@include|@extends|{{|}}|{!!|!!})/i', $content) !== 1
            && preg_match('/<\s*script\b|\son[a-z]+\s*=|javascript:|data:text\/html/i', $content) !== 1;
    }

    public function sanitize(string $content): string
    {
        $content = trim($content);

        if (! $this->isSafe($content) && preg_match('/(<\?php|<\?=|@php|@endphp|@foreach|@if|@include|@extends|{{|}}|{!!|!!})/i', $content) === 1) {
            throw ValidationException::withMessages(['content' => 'Comments cannot contain executable PHP or Blade syntax.']);
        }

        if (! $this->isSafe($content)) {
            throw ValidationException::withMessages(['content' => 'Comments cannot contain executable HTML.']);
        }

        $clean = strip_tags($content, '<p><br><strong><b><em><i><u><a><ul><ol><li><blockquote><code>');
        $clean = preg_replace('/\s(style|class|id)\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $clean) ?? $clean;

        return trim($clean);
    }

    public function linkCount(string $content): int
    {
        preg_match_all('/https?:\/\/|<a\s/i', $content, $matches);

        return count($matches[0] ?? []);
    }
}
