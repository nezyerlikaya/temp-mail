<?php

namespace App\Services\Pages;

use App\Models\Page;
use Illuminate\Validation\ValidationException;

class PageLifecycleService
{
    /** @return array<int, string> */
    public function allowedTransitions(Page $page): array
    {
        return match ($page->status) {
            'draft' => ['published', 'hidden', 'trashed'],
            'published' => ['hidden', 'draft', 'trashed'],
            'hidden' => ['draft', 'published', 'trashed'],
            'trashed' => ['draft'],
            default => ['draft'],
        };
    }

    public function assertCanTransition(Page $page, string $target): void
    {
        if (! in_array($target, $this->allowedTransitions($page), true)) {
            throw ValidationException::withMessages([
                'status' => 'This page cannot move from '.str($page->status)->headline().' to '.str($target)->headline().'.',
            ]);
        }
    }
}
