<?php

namespace App\Services\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuthorProfileService
{
    public function profilesFor(User $actor): LengthAwarePaginator
    {
        $query = User::query()
            ->whereIn('role', [UserRole::Owner->value, UserRole::Admin->value, UserRole::Editor->value, UserRole::Author->value])
            ->orderByDesc('featured_author')
            ->orderBy('name');

        if (! in_array($actor->role, [UserRole::Owner->value, UserRole::Admin->value], true)) {
            $query->whereKey($actor->getKey());
        }

        return $query->paginate(12)->withQueryString();
    }

    /** @return array{ready: bool, completion: int, missing: array<int, string>, attribution_name: string, public_state: string, social_count: int} */
    public function summary(User $user): array
    {
        $missing = collect([
            'Display name' => filled($user->display_name),
            'Public author slug' => filled($user->public_author_slug),
            'Author bio' => filled($user->author_bio),
            'Website' => filled($user->website),
        ])->reject()->keys()->values()->all();

        return [
            'ready' => $missing === [],
            'completion' => (int) round((4 - count($missing)) / 4 * 100),
            'missing' => $missing,
            'attribution_name' => $user->display_name ?: $user->name,
            'public_state' => $user->author_profile_active && $user->status === 'active' ? 'active' : 'hidden',
            'social_count' => collect($user->social_links ?? [])->filter()->count(),
        ];
    }

    /** @return array<string, string> */
    public function socialPlatforms(): array
    {
        return ['x' => 'X / Twitter', 'linkedin' => 'LinkedIn', 'github' => 'GitHub'];
    }
}
