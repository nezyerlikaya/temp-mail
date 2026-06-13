<?php

namespace App\Actions\Typography;

use App\Models\FontFamily;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Typography\FontAssignmentService;

class DeactivateFontFamilyAction
{
    public function __construct(
        private readonly FontAssignmentService $fonts,
        private readonly AuditLogger $audit,
    ) {}

    public function __invoke(FontFamily $family, User $actor): FontFamily
    {
        $updated = $this->fonts->setActive($family, false, $actor);

        $this->audit->record('typography.font_family_deactivated', $actor, null, [
            'font_family' => $updated->slug,
        ], ['module' => 'typography', 'target' => $updated]);

        return $updated;
    }
}
