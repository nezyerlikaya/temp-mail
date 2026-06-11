<?php

namespace App\Actions\Users;

use App\Enums\UserRole;
use App\Events\UserRoleChanged;
use App\Models\User;
use App\Services\Users\AdminProtectionGuard;
use App\Services\Users\UserAuditLogger;
use Illuminate\Support\Facades\DB;

class RoleAssignmentAction
{
    public function __construct(
        private readonly AdminProtectionGuard $protection,
        private readonly UserAuditLogger $audit,
    ) {}

    public function handle(User $actor, User $subject, UserRole $newRole): User
    {
        $this->protection->assertRoleChangeAllowed($actor, $subject, $newRole);

        if ($subject->role === $newRole->value) {
            return $subject;
        }

        return DB::transaction(function () use ($actor, $subject, $newRole): User {
            $oldRole = (string) $subject->role;

            $subject->forceFill([
                'role' => $newRole->value,
                'is_admin' => $newRole->hasAdminAccess(),
            ])->save();

            $this->audit->record($actor, $subject, 'user.role_changed', [
                'old_role' => $oldRole,
                'new_role' => $newRole->value,
            ]);

            UserRoleChanged::dispatch($actor, $subject, $oldRole, $newRole->value);

            return $subject->refresh();
        });
    }
}
