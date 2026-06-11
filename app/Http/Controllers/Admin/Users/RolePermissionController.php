<?php

namespace App\Http\Controllers\Admin\Users;

use App\Actions\Users\RoleAssignmentAction;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateUserRoleRequest;
use App\Models\User;
use App\Services\Users\RolePermissionResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    public function index(Request $request, RolePermissionResolver $resolver): View
    {
        Gate::authorize('admin.roles-permissions.view');

        return view('dashboard.roles-permissions.index', [
            'adminUser' => $request->user(),
            'roles' => UserRole::cases(),
            'roleSummaries' => $resolver->roleSummaries(),
            'permissionMatrix' => $resolver->permissionMatrix(),
            'roleOptions' => $resolver->roleOptions(),
            'users' => User::query()->orderByRaw("case role when 'owner' then 1 when 'admin' then 2 else 3 end")
                ->orderBy('name')
                ->paginate(12),
        ]);
    }

    public function update(
        UpdateUserRoleRequest $request,
        User $user,
        RoleAssignmentAction $assignRole,
    ): RedirectResponse {
        $role = UserRole::from($request->validated('role'));
        $assignRole->handle($request->user(), $user, $role);

        return redirect()
            ->route('admin.roles-permissions.index')
            ->with('status', $user->name.' is now '.$role->label().'.');
    }
}
