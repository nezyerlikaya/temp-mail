<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Services\Users\UserAuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateAvatarAction
{
    public function __construct(private readonly UserAuditLogger $audit) {}

    /** @param array{avatar_media_id?: int|null, avatar_color: string, remove_avatar?: bool} $avatar */
    public function handle(User $actor, User $user, array $avatar): User
    {
        return DB::transaction(function () use ($actor, $user, $avatar): User {
            $oldMediaId = $user->avatar_media_id;
            $oldColor = $user->avatar_color;

            $user->forceFill([
                'avatar_media_id' => ($avatar['remove_avatar'] ?? false) ? null : ($avatar['avatar_media_id'] ?? $user->avatar_media_id),
                'avatar_color' => $avatar['avatar_color'],
            ])->save();

            if ($user->wasChanged(['avatar_media_id', 'avatar_color'])) {
                $this->audit->record($actor, $user, 'user.avatar_updated', [
                    'old_media_id' => $oldMediaId,
                    'new_media_id' => $user->avatar_media_id,
                    'old_color' => $oldColor,
                    'new_color' => $user->avatar_color,
                ]);
            }

            return $user->refresh();
        });
    }
}
