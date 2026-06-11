<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Services\Users\UserAuditLogger;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateAuthorProfileAction
{
    public function __construct(private readonly UserAuditLogger $audit) {}

    /** @param array<string, mixed> $profile */
    public function handle(User $actor, User $user, array $profile): User
    {
        return DB::transaction(function () use ($actor, $user, $profile): User {
            $before = $user->only(array_keys($profile));

            $user->fill($profile);
            $user->save();

            $changes = collect($user->getChanges())
                ->except(['updated_at'])
                ->mapWithKeys(fn (mixed $value, string $key): array => [
                    $key => ['old' => Arr::get($before, $key), 'new' => $value],
                ])->all();

            if ($changes !== []) {
                $this->audit->record($actor, $user, 'user.author_profile_updated', ['changes' => $changes]);
            }

            return $user->refresh();
        });
    }
}
