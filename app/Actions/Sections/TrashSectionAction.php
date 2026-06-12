<?php

namespace App\Actions\Sections;

use App\Models\Section;
use App\Models\User;
use App\Services\Sections\SectionAuditLogger;
use App\Services\Sections\SectionLifecycleService;

class TrashSectionAction
{
    public function __construct(
        private readonly SectionLifecycleService $lifecycle,
        private readonly SectionAuditLogger $audit,
    ) {}

    public function handle(User $actor, Section $section): Section
    {
        $this->lifecycle->assertCanTransition($section, 'trashed');

        $section->update([
            'status' => 'trashed',
            'updated_by' => $actor->id,
            'trashed_at' => now(),
        ]);

        $this->audit->record('section.trashed', 'Trash section', $actor, $section);

        return $section->refresh();
    }
}
