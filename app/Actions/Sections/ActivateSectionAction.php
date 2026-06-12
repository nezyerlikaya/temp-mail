<?php

namespace App\Actions\Sections;

use App\Models\Section;
use App\Models\User;
use App\Services\Sections\SectionAuditLogger;
use App\Services\Sections\SectionLifecycleService;

class ActivateSectionAction
{
    public function __construct(
        private readonly SectionLifecycleService $lifecycle,
        private readonly SectionAuditLogger $audit,
    ) {}

    public function handle(User $actor, Section $section): Section
    {
        $this->lifecycle->assertCanTransition($section, 'active');

        $section->update([
            'status' => 'active',
            'updated_by' => $actor->id,
            'trashed_at' => null,
        ]);

        $this->audit->record('section.activated', 'Activate section', $actor, $section);

        return $section->refresh();
    }
}
