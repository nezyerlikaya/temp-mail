<?php

namespace App\Http\Controllers\Admin\Users;

use App\Actions\Users\UpdateAuthorProfileAction;
use App\Actions\Users\UpdateAvatarAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateAuthorProfileRequest;
use App\Http\Requests\Users\UpdateAvatarRequest;
use App\Models\User;
use App\Services\Media\MediaPickerSearchService;
use App\Services\Users\AuthorProfileService;
use App\Services\Users\AvatarResolver;
use App\Services\Users\MembershipSummaryResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuthorProfileController extends Controller
{
    public function index(Request $request, AuthorProfileService $profiles, AvatarResolver $avatars): View
    {
        Gate::authorize('admin.author-profiles.view');

        $users = $profiles->profilesFor($request->user());

        return view('dashboard.author-profiles.index', [
            'adminUser' => $request->user(),
            'users' => $users,
            'profileSummaries' => $users->getCollection()->mapWithKeys(fn (User $user): array => [
                $user->id => $profiles->summary($user),
            ]),
            'avatars' => $users->getCollection()->mapWithKeys(fn (User $user): array => [
                $user->id => $avatars->resolve($user),
            ]),
        ]);
    }

    public function edit(
        Request $request,
        User $user,
        AuthorProfileService $profiles,
        AvatarResolver $avatars,
        MembershipSummaryResolver $memberships,
        MediaPickerSearchService $mediaPicker,
    ): View {
        Gate::authorize('updateAuthorProfile', $user);

        return view('dashboard.author-profiles.edit', [
            'adminUser' => $request->user(),
            'profileUser' => $user,
            'authorSummary' => $profiles->summary($user),
            'socialPlatforms' => $profiles->socialPlatforms(),
            'avatar' => $avatars->resolve($user),
            'mediaPickerAssets' => $mediaPicker->options(['type' => 'image']),
            'canSelectMedia' => $request->user()?->can('admin.media-library.select') ?? false,
            'canUploadThroughPicker' => $request->user()?->can('admin.media-library.upload-through-picker') ?? false,
            'membership' => $memberships->resolve($user),
        ]);
    }

    public function update(UpdateAuthorProfileRequest $request, User $user, UpdateAuthorProfileAction $update): RedirectResponse
    {
        $update->handle($request->user(), $user, $request->validated());

        return redirect()->route($this->returnRoute($request, $user), $this->returnParameters($request, $user))
            ->with('status', 'Author profile updated.');
    }

    public function updateAvatar(UpdateAvatarRequest $request, User $user, UpdateAvatarAction $update): RedirectResponse
    {
        $update->handle($request->user(), $user, $request->validated());

        return redirect()->route($this->returnRoute($request, $user), $this->returnParameters($request, $user))
            ->with('status', 'Avatar profile updated.');
    }

    private function returnRoute(Request $request, User $user): string
    {
        return $request->user()->can('view', $user)
            ? 'admin.people-identity.show'
            : 'admin.author-profiles.edit';
    }

    /** @return array<int, User> */
    private function returnParameters(Request $request, User $user): array
    {
        return [$user];
    }
}
