<?php

namespace App\Actions\Typography;

use App\Models\Locale;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Typography\FontAssignmentService;

class ResetLocaleFontOverrideAction
{
    public function __construct(
        private readonly FontAssignmentService $fonts,
        private readonly AuditLogger $audit,
    ) {}

    public function __invoke(Locale $locale, User $actor): int
    {
        $deleted = $this->fonts->resetLocaleOverride($locale, $actor);

        $this->audit->record('typography.locale_override_reset', $actor, null, [
            'locale' => $locale->locale,
            'deleted_assignments' => $deleted,
        ], ['module' => 'typography', 'target' => $locale]);

        return $deleted;
    }
}
