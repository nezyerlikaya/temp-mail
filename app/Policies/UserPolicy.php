<?php

namespace App\Policies;

use App\Models\User;
use App\Services\Users\RolePermissionResolver;

class UserPolicy
{
    public function __construct(private readonly RolePermissionResolver $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'admin.people-identity.view');
    }

    public function view(User $actor, User $subject): bool
    {
        return $this->permissions->allows($actor, 'admin.people-identity.view');
    }

    public function update(User $actor, User $subject): bool
    {
        return $this->permissions->allows($actor, 'admin.roles-permissions.manage');
    }
}
