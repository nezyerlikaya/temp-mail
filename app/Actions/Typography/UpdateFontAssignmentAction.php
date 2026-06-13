<?php

namespace App\Actions\Typography;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Typography\FontAssignmentService;
use Illuminate\Support\Collection;

class UpdateFontAssignmentAction
{
    public function __construct(
        private readonly FontAssignmentService $fonts,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, array{font_family_slug: string, fallback_stack?: array<int, string>}>  $assignments
     * @return Collection<int, mixed>
     */
    public function __invoke(string $scope, string $scopeKey, array $assignments, User $actor): Collection
    {
        $updated = $this->fonts->updateAssignments($scope, $scopeKey, $assignments, $actor);

        $this->audit->record('typography.assignments_updated', $actor, null, [
            'scope' => $scope,
            'scope_key' => $scopeKey,
            'usages' => array_keys($assignments),
        ], ['module' => 'typography']);

        return $updated;
    }
}
