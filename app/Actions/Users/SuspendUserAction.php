<?php

namespace App\Actions\Users;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Validation\ValidationException;

class SuspendUserAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handle(User $actor, User $user): User
    {
        $role = UserRole::tryFrom((string) $user->role) ?? UserRole::Member;

        if ($actor->is($user) || $role->isCritical()) {
            throw ValidationException::withMessages(['operational_action' => 'The selected protected account cannot be suspended from an abuse case.']);
        }

        $user->forceFill(['status' => 'suspended'])->save();
        $this->audit->record('user.suspended', $actor, $user, ['source' => 'abuse_case'], ['module' => 'people', 'target' => $user]);

        return $user->refresh();
    }
}
