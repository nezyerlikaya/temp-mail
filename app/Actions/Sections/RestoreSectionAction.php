<?php

namespace App\Actions\Sections;

use App\Models\Section;
use App\Models\User;
use App\Services\Sections\SectionAuditLogger;
use App\Services\Sections\SectionLifecycleService;

class RestoreSectionAction
{
    public function __construct(
        private readonly SectionLifecycleService $lifecycle,
        private readonly SectionAuditLogger $audit,
    ) {}

    public function handle(User $actor, Section $section): Section
    {
        $this->lifecycle->assertCanTransition($section, 'draft');

        $section->update([
            'status' => 'draft',
            'updated_by' => $actor->id,
            'trashed_at' => null,
        ]);

        $this->audit->record('section.restored', 'Restore section', $actor, $section);

        return $section->refresh();
    }
}
