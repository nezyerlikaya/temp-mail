<?php

namespace App\Services\Sections;

use App\Models\Section;
use Illuminate\Validation\ValidationException;

class SectionLifecycleService
{
    /** @return array<int, string> */
    public function allowedTransitions(Section $section): array
    {
        return match ($section->status) {
            'draft' => ['active', 'hidden', 'trashed'],
            'active' => ['hidden', 'draft', 'trashed'],
            'hidden' => ['active', 'draft', 'trashed'],
            'trashed' => ['draft'],
            default => ['draft'],
        };
    }

    public function assertCanTransition(Section $section, string $target): void
    {
        if (! in_array($target, $this->allowedTransitions($section), true)) {
            throw ValidationException::withMessages([
                'status' => 'This section cannot move from '.str($section->status)->headline().' to '.str($target)->headline().'.',
            ]);
        }
    }
}
