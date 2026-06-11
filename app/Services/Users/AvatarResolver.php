<?php

namespace App\Services\Users;

use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Media\MediaPickerSearchService;

class AvatarResolver
{
    public function __construct(private readonly MediaPickerSearchService $picker) {}

    /** @return array{initials: string, color: string, media_id: int|null, has_media: bool, media_library_ready: bool, label: string, selected: array<string, mixed>|null} */
    public function resolve(User $user): array
    {
        $name = trim((string) ($user->display_name ?: $user->name));
        $selected = $this->picker->option($user->avatar_media_id ? MediaAsset::query()->find($user->avatar_media_id) : null);
        $initials = str($name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => str($part)->substr(0, 1)->upper()->toString())
            ->implode('');

        return [
            'initials' => $initials ?: '?',
            'color' => $user->avatar_color ?: '#0f766e',
            'media_id' => $user->avatar_media_id,
            'has_media' => $user->avatar_media_id !== null,
            'media_library_ready' => $selected !== null,
            'label' => $name !== '' ? $name.' avatar' : 'User avatar',
            'selected' => $selected,
        ];
    }
}
