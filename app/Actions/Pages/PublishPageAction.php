<?php

namespace App\Actions\Pages;

class PublishPageAction
{
    public function statusForIntent(?string $intent, string $fallback = 'draft'): string
    {
        return match ($intent) {
            'publish' => 'published',
            'hide' => 'hidden',
            'save_draft' => 'draft',
            default => $fallback,
        };
    }
}
