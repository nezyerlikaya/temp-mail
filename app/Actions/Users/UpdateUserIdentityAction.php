<?php

namespace App\Actions\Users;

use App\Events\UserIdentityUpdated;
use App\Models\User;
use App\Services\Users\UserAuditLogger;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateUserIdentityAction
{
    public function __construct(private readonly UserAuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $identity
     */
    public function handle(User $actor, User $user, array $identity): User
    {
        return DB::transaction(function () use ($actor, $user, $identity): User {
            $before = $user->only(array_keys($identity));

            $user->fill($identity);
            $user->save();

            $changes = collect($user->getChanges())
                ->except(['updated_at'])
                ->mapWithKeys(fn (mixed $value, string $key): array => [
                    $key => [
                        'old' => Arr::get($before, $key),
                        'new' => $value,
                    ],
                ])
                ->all();

            if ($changes !== []) {
                $this->audit->record($actor, $user, 'user.identity_updated', [
                    'changed_keys' => array_keys($changes),
                ]);

                UserIdentityUpdated::dispatch($actor, $user, $changes);
            }

            return $user->refresh();
        });
    }
}
