<?php

namespace App\Actions\Sections;

use App\Models\Section;
use App\Models\User;
use App\Services\Sections\SectionAuditLogger;
use App\Services\Sections\SectionLifecycleService;

class HideSectionAction
{
    public function __construct(
        private readonly SectionLifecycleService $lifecycle,
        private readonly SectionAuditLogger $audit,
    ) {}

    public function handle(User $actor, Section $section): Section
    {
        $this->lifecycle->assertCanTransition($section, 'hidden');

        $section->update([
            'status' => 'hidden',
            'updated_by' => $actor->id,
            'trashed_at' => null,
        ]);

        $this->audit->record('section.hidden', 'Hide section', $actor, $section);

        return $section->refresh();
    }
}
