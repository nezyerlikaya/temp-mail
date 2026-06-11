<?php

namespace App\Http\Controllers\Admin\Users;

use App\Actions\Users\UpdateUserIdentityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateUserIdentityRequest;
use App\Models\User;
use App\Services\Users\UserProfileService;
use App\Services\Users\UserSearchService;
use App\Services\Users\UserStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(
        Request $request,
        UserSearchService $search,
        UserProfileService $profiles,
        UserStatusService $statuses,
    ): View {
        Gate::authorize('viewAny', User::class);

        return view('dashboard.people-identity.index', [
            'adminUser' => $request->user(),
            'users' => $search->search($request),
            'roles' => $profiles->roles(),
            'statuses' => $statuses->statuses(),
        ]);
    }

    public function show(Request $request, User $user): View
    {
        Gate::authorize('view', $user);

        return view('dashboard.people-identity.show', [
            'adminUser' => $request->user(),
            'profileUser' => $user,
        ]);
    }

    public function edit(Request $request, User $user, UserProfileService $profiles): View
    {
        Gate::authorize('update', $user);

        return view('dashboard.people-identity.edit', [
            'adminUser' => $request->user(),
            ...$profiles->formData($user),
        ]);
    }

    public function update(
        UpdateUserIdentityRequest $request,
        User $user,
        UpdateUserIdentityAction $updateIdentity,
    ): RedirectResponse {
        $updatedUser = $updateIdentity->handle($request->user(), $user, $request->validated());

        return redirect()
            ->route('admin.people-identity.show', $updatedUser)
            ->with('status', 'Identity profile updated.');
    }
}
