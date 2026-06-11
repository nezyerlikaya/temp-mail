<?php

namespace App\Services\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AdminProtectionGuard
{
    public function assertRoleChangeAllowed(User $actor, User $subject, UserRole $newRole): void
    {
        $actorRole = UserRole::tryFrom((string) $actor->role) ?? UserRole::Member;
        $currentRole = UserRole::tryFrom((string) $subject->role) ?? UserRole::Member;

        if (! in_array($actorRole, [UserRole::Owner, UserRole::Admin], true)) {
            $this->fail('role', 'Only an owner or administrator can assign roles.');
        }

        if (($currentRole === UserRole::Owner || $newRole === UserRole::Owner) && $actorRole !== UserRole::Owner) {
            $this->fail('role', 'Only an owner can assign or modify the owner role.');
        }

        if ($actor->is($subject) && $currentRole->isCritical() && $newRole !== $currentRole) {
            $this->fail('role', 'You cannot remove your own critical access. Ask another protected administrator.');
        }

        if ($currentRole === UserRole::Owner && $newRole !== UserRole::Owner && $this->countRole(UserRole::Owner) <= 1) {
            $this->fail('role', 'The last owner cannot be downgraded. Assign another owner first.');
        }

        if ($currentRole->isCritical() && ! $newRole->isCritical() && $this->criticalAccountCount() <= 1) {
            $this->fail('role', 'The last owner or administrator cannot lose critical access.');
        }
    }

    public function assertDeletionAllowed(User $actor, User $subject): void
    {
        $role = UserRole::tryFrom((string) $subject->role) ?? UserRole::Member;

        if ($role === UserRole::Owner) {
            $this->fail('user', 'Owner accounts cannot be deleted.');
        }

        if ($actor->is($subject) && $role->isCritical()) {
            $this->fail('user', 'You cannot delete your own protected administrator account.');
        }

        if ($role->isCritical() && $this->criticalAccountCount() <= 1) {
            $this->fail('user', 'The last owner or administrator cannot be deleted.');
        }
    }

    private function countRole(UserRole $role): int
    {
        return User::query()->where('role', $role->value)->count();
    }

    private function criticalAccountCount(): int
    {
        return User::query()->whereIn('role', [UserRole::Owner->value, UserRole::Admin->value])->count();
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
