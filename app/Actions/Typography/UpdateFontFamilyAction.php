<?php

namespace App\Actions\Typography;

use App\Models\FontFamily;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Typography\FontAssignmentService;

class UpdateFontFamilyAction
{
    public function __construct(
        private readonly FontAssignmentService $fonts,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function __invoke(FontFamily $family, array $data, User $actor): FontFamily
    {
        $updated = $this->fonts->updateFamily($family, $data, $actor);

        $this->audit->record('typography.font_family_updated', $actor, null, [
            'font_family' => $updated->slug,
            'font_display' => $updated->font_display,
            'local_file_ready' => $updated->local_file_ready,
            'media_ready' => $updated->media_ready,
        ], ['module' => 'typography', 'target' => $updated]);

        return $updated;
    }
}
