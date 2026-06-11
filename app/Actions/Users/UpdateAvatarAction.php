<?php

namespace App\Actions\Users;

use App\Actions\Media\AttachMediaUsageAction;
use App\Actions\Media\DetachMediaUsageAction;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Users\UserAuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateAvatarAction
{
    public function __construct(
        private readonly UserAuditLogger $audit,
        private readonly AttachMediaUsageAction $attachMediaUsage,
        private readonly DetachMediaUsageAction $detachMediaUsage,
    ) {}

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
                if ($oldMediaId !== $user->avatar_media_id) {
                    $usage = [
                        'module' => 'avatars',
                        'usage_context' => 'user_profile',
                        'slot' => 'avatar_media_id',
                        'usable_type' => User::class,
                        'usable_id' => (string) $user->id,
                    ];

                    $this->detachMediaUsage->handle($actor, $usage);

                    $asset = $user->avatar_media_id ? MediaAsset::query()->find($user->avatar_media_id) : null;
                    if ($asset) {
                        $this->attachMediaUsage->handle($actor, $asset, [
                            ...$usage,
                            'label' => $user->display_name ?: $user->name,
                        ]);
                    }
                }

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
